<?php defined('BASEPATH') || exit('No direct script access allowed');

/** Explicit, request-local scope. No controller is instrumented automatically. */
class Module_audit_recorder
{
    private $ci;
    private $state = 'idle';
    private $context = array();
    private $dataKeys = array();
    private $legacyCount = 0;

    public function __construct()
    {
        $this->ci =& get_instance();
        register_shutdown_function(array($this, 'finish_interrupted'));
    }

    /** Context accepts data/sql only. Actor identity is captured from the session. */
    public function begin($module, $methode, $context = array(), $dataKeys = array())
    {
        if ($this->state !== 'idle') { return false; }
        try {
            foreach (array($module, $methode) as $name) {
                if (!is_string($name) || trim($name) === '' || !preg_match('//u', $name)
                    || mb_strlen($name, 'UTF-8') > 50) {
                    throw new InvalidArgumentException('Invalid endpoint');
                }
            }
            if (strtolower($module) === 'audit_trail') { return false; }
            if (!is_array($dataKeys) || count($dataKeys) > 50) { throw new InvalidArgumentException('Invalid allowlist'); }
            $keys = array();
            foreach ($dataKeys as $key) {
                if (!is_string($key) || !preg_match('/^[a-z][a-z0-9_]{0,63}$/i', $key) || $this->private_key($key)) {
                    throw new InvalidArgumentException('Unsafe allowlist');
                }
                $keys[] = $key;
            }
            $initial = $this->filter_context($context, $keys);
            $session = isset($this->ci->session) ? $this->ci->session->userdata('app_session') : array();
            $session = is_array($session) ? $session : array();
            $this->context = array_merge($initial, array('module' => $module, 'methode' => $methode,
                'username' => $session['username'] ?? null, 'created_by' => $session['id_user'] ?? null));
            $this->dataKeys = $keys;
            $this->state = 'active';
            return true;
        } catch (Throwable $error) {
            log_message('error', 'Audit Trail: invalid recorder scope.');
            return false;
        }
    }

    public function is_active() { return $this->state === 'active'; }

    /** Keep late legacy calls from creating a second event after completion. */
    public function owns_request() { return $this->state !== 'idle'; }

    /** Legacy status/messages never determine the endpoint's final business result. */
    public function defer($message, $status = null, $context = array())
    {
        if (!$this->owns_request()) { return false; }
        if (!$this->is_active()) { return true; }
        $this->legacyCount++;
        try {
            if (!is_array($context)) { throw new InvalidArgumentException('Invalid context'); }
            $candidate = array();
            foreach (array('data', 'sql') as $field) {
                if (array_key_exists($field, $context)) { $candidate[$field] = $context[$field]; }
            }
            $filtered = $this->filter_context($candidate, $this->dataKeys);
            $data = array_merge($this->context['data'], $filtered['data']);
            $this->check_size($data);
            $this->context['data'] = $data;
            if (isset($filtered['sql']) && $filtered['sql'] !== '') { $this->context['sql'] = $filtered['sql']; }
        } catch (Throwable $error) {
            log_message('error', 'Audit Trail: legacy recorder metadata omitted.');
        }
        // Accepted means deferred, not that a database insert has happened.
        return true;
    }

    /** Call only after the endpoint's real result/commit/file generation is known. */
    public function complete($message, $status = null, $context = array())
    {
        if (!$this->is_active()) { return false; }
        try {
            if (!is_string($message) || trim($message) === '' || !preg_match('//u', $message)
                || strlen($message) > 65535 || !in_array($status, array(null, 0, 1, '0', '1'), true)) {
                throw new InvalidArgumentException('Invalid completion');
            }
            $final = $this->filter_context($context, $this->dataKeys);
            $event = $this->context;
            $event['data'] = array_merge($event['data'], $final['data']);
            $event['data']['legacy_event_count'] = $this->legacyCount;
            $this->check_size($event['data']);
            if (array_key_exists('sql', $final)) { $event['sql'] = $final['sql']; }
        } catch (Throwable $error) {
            log_message('error', 'Audit Trail: invalid recorder completion.');
            return false;
        }
        // Close before writing: writer failure must never become a false business interruption.
        $this->state = 'completed';
        $this->context = array();
        try {
            return audit_log($message, $status, $event);
        } catch (Throwable $error) {
            log_message('error', 'Audit Trail: recorder writer unavailable.');
            return false;
        }
    }

    public function finish_interrupted()
    {
        if (!$this->is_active()) { return; }
        $event = $this->context;
        $event['data']['outcome'] = 'interrupted';
        $event['data']['legacy_event_count'] = $this->legacyCount;
        // An aborted flow has no confirmed SQL outcome; omit buffered SQL.
        unset($event['sql']);
        $this->state = 'completed';
        $this->context = array();
        try {
            audit_log('Aktivitas ' . $event['module'] . '/' . $event['methode'] . ' terhenti sebelum hasil akhir', 0, $event);
        } catch (Throwable $error) {
            log_message('error', 'Audit Trail: interrupted activity writer unavailable.');
        }
    }

    private function filter_context($context, $keys)
    {
        if (!is_array($context) || array_diff(array_keys($context), array('data', 'sql'))) {
            throw new InvalidArgumentException('Invalid context');
        }
        $data = $context['data'] ?? array();
        if (!is_array($data)) { throw new InvalidArgumentException('Invalid data'); }
        $result = array('data' => array());
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) { continue; }
            $value = $data[$key];
            if (is_array($value)) {
                // Batch IDs are supported; nested request/user records are not.
                if (count($value) > 100 || array_values($value) !== $value) {
                    throw new InvalidArgumentException('Invalid list');
                }
                foreach ($value as $item) { $this->check_scalar($item); }
            } else { $this->check_scalar($value); }
            $result['data'][$key] = $value;
        }
        $this->check_size($result['data']);
        if (array_key_exists('sql', $context)) {
            $sql = $context['sql'];
            if ($sql !== null && (!is_string($sql) || !preg_match('//u', $sql) || strlen($sql) > 65535)) {
                throw new InvalidArgumentException('Invalid SQL');
            }
            $result['sql'] = $sql;
        }
        return $result;
    }

    private function check_scalar($value)
    {
        if ($value !== null && !is_scalar($value)) { throw new InvalidArgumentException('Invalid scalar'); }
        if (is_string($value) && (!preg_match('//u', $value) || mb_strlen($value, 'UTF-8') > 512)) {
            throw new InvalidArgumentException('Invalid text');
        }
        if (is_float($value) && !is_finite($value)) { throw new InvalidArgumentException('Invalid number'); }
    }

    private function check_size($data)
    {
        if (strlen(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)) > 24576) {
            throw new InvalidArgumentException('Oversized metadata');
        }
    }

    private function private_key($key)
    {
        $key = strtolower(preg_replace('/[^a-z0-9]/i', '', $key));
        return preg_match('/password|passwd|passphrase|secret|token|cookie|session|authorization|apikey|otp|credential|email|phone|telepon|rekening|bankaccount|address|alamat|fullname|nmlengkap/', $key)
            || in_array($key, array('pwd','pass','pin','username','createdby','iduser','user','email','phone',
                'telepon','rekening','norek','bankaccount','address','alamat','nmlengkap','name','nama'), true);
    }
}
