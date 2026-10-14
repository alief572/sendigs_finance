<?php defined('BASEPATH') || exit('No direct script access allowed');

/** Writes to the existing logs table using an independent mysqli connection. */
class Audit_logger
{
    protected $ci;
    private $connection;
    private $connectionAttempted = false;

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    public function write($message, $status = null, $context = array())
    {
        try {
            $row = $this->normalize($message, $status, $context);
            if (strtolower((string) $row['module']) === 'audit_trail') {
                return true;
            }
            if (!$this->persist($row)) {
                log_message('error', 'Audit Trail: activity could not be stored.');
                return false;
            }
            return true;
        } catch (Throwable $error) {
            // Never log exception text: drivers may include credentials or SQL values.
            log_message('error', 'Audit Trail: invalid activity or writer failure.');
            return false;
        }
    }

    private function normalize($message, $status, $context)
    {
        if (!is_string($message) || trim($message) === '' || !is_array($context)
            || !in_array($status, array(null, 0, 1, '0', '1'), true)) {
            throw new InvalidArgumentException('Invalid activity');
        }
        $session = isset($this->ci->session) ? $this->ci->session->userdata('app_session') : array();
        $session = is_array($session) ? $session : array();
        $router = $this->ci->router;
        $module = method_exists($router, 'fetch_module') ? $router->fetch_module() : null;
        $defaults = array(
            'module' => $module ?: $router->fetch_class(),
            'methode' => $router->fetch_method(),
            'username' => isset($session['username']) ? $session['username'] : null,
            'created_by' => isset($session['id_user']) ? $session['id_user'] : null,
            'data' => null, 'sql' => null
        );
        if (array_diff(array_keys($context), array_keys($defaults))) {
            throw new InvalidArgumentException('Invalid context');
        }
        $values = array_merge($defaults, $context);
        foreach (array('module' => 50, 'methode' => 50, 'username' => 30) as $field => $limit) {
            $value = $values[$field];
            if ($value !== null && (!is_string($value) || !preg_match('//u', $value)
                || ($field === 'username' ? strlen($value) : mb_strlen($value, 'UTF-8')) > $limit)) {
                throw new InvalidArgumentException('Invalid metadata');
            }
        }
        if ($values['created_by'] === '') { $values['created_by'] = null; }
        if ($values['created_by'] !== null && ((!is_int($values['created_by']) && !is_string($values['created_by']))
            || filter_var($values['created_by'], FILTER_VALIDATE_INT) === false
            || $values['created_by'] < 0 || $values['created_by'] > 2147483647)) {
            throw new InvalidArgumentException('Invalid user ID');
        }
        $data = $values['data'];
        if (is_string($data)) {
            $data = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException('Payload must be structured JSON');
            }
        }
        $data = $data === null ? null : json_encode($this->mask($data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $sql = $values['sql'] === null ? null : $this->sql_structure($values['sql']);
        $ip = $this->ci->input->ip_address();
        if ($ip === '::1') { $ip = '127.0.0.1'; }
        // The existing column is varchar(15). Never store a misleading truncated IPv6.
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) { $ip = null; }
        $agent = $this->ci->input->user_agent();
        $agent = is_string($agent) ? $agent : '';
        $platform = 'Unknown';
        foreach (array('Android' => 'Android', 'iPhone|iPad' => 'iOS', 'Windows' => 'Windows', 'Macintosh|Mac OS' => 'macOS', 'Linux' => 'Linux') as $pattern => $label) {
            if (preg_match('/' . $pattern . '/i', $agent)) { $platform = $label; break; }
        }
        $date = new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta'));
        return array(
            'id' => bin2hex(random_bytes(16)),
            'module' => $this->text($values['module']), 'methode' => $this->text($values['methode']),
            'username' => $values['username'], 'created_by' => $values['created_by'] === null ? null : (int) $values['created_by'],
            'data' => $this->text($data), 'sql' => $this->text($sql), 'ip_address' => $ip,
            'agent' => $this->text($this->mask_text($agent)), 'platform' => $platform,
            'status' => $status === null ? null : (string) $status,
            'message' => $this->text($this->mask_text($message)), 'created_at' => $date->format('Y-m-d H:i:s')
        );
    }

    private function text($value)
    {
        if ($value === null) { return null; }
        if (!preg_match('//u', $value) || strlen($value) > 65535) {
            throw new InvalidArgumentException('Invalid text');
        }
        // logs uses utf8mb3, which cannot represent supplementary Unicode characters.
        return preg_replace('/[\x{10000}-\x{10FFFF}]/u', "\xEF\xBF\xBD", $value);
    }

    private function sensitive_key($key)
    {
        $key = strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $key));
        return preg_match('/password|passwd|passphrase|secret|token|cookie|session|authorization|apikey|otp|credential/', $key)
            || in_array($key, array('pwd', 'pass', 'pin'), true);
    }

    private function mask($value, $depth = 0)
    {
        if ($depth > 16 || is_object($value) || is_resource($value)) {
            throw new InvalidArgumentException('Invalid payload');
        }
        if (is_array($value)) {
            $result = array();
            foreach ($value as $key => $item) {
                $result[$key] = $this->sensitive_key($key) ? '[REDACTED]' : $this->mask($item, $depth + 1);
            }
            return $result;
        }
        return is_string($value) ? $this->mask_text($value) : $value;
    }

    private function mask_text($value)
    {
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return json_encode($this->mask($decoded), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        }
        $value = preg_replace('/\bBearer\s+[^\s,;]+/i', 'Bearer [REDACTED]', $value);
        return preg_replace('/\b(password|passwd|pwd|otp|ga_secret|secret|[a-z_]*token|api_key|authorization|cookie|session)\b\s*[:=]\s*("[^"\r\n]*"|\x27[^\x27\r\n]*\x27|[^\s,;&]+)/i', '$1=[REDACTED]', $value);
    }

    /** Keep identifiers/operators; remove all literals and comments, fail closed. */
    private function sql_structure($sql)
    {
        if (!is_string($sql) || strlen($sql) > 65535) { throw new InvalidArgumentException('Invalid SQL'); }
        $pattern = '~\G(?:\s+|/\*[\s\S]*?\*/|--[^\r\n]*|\#[^\r\n]*|\x27(?:\\\\[\s\S]|\x27\x27|[^\x27\\\\])*\x27|"(?:\\\\[\s\S]|""|[^"\\\\])*"|`(?:``|[^`])*`|0x[0-9a-f]+|\b\d+(?:\.\d+)?(?:e[+-]?\d+)?\b|[a-z_][a-z0-9_$]*|[(),.;=<>!+*/%?:@|&^\x7E-])~i';
        $offset = 0; $result = '';
        while ($offset < strlen($sql)) {
            if (!preg_match($pattern, $sql, $match, 0, $offset)) { return '[SQL omitted: unrecognized syntax]'; }
            $token = $match[0]; $offset += strlen($token);
            if (preg_match('~^(?:/\*|--|\#)~', $token)) { $result .= ' '; continue; }
            if ($token[0] === "'" || $token[0] === '"' || preg_match('/^(?:\d|0x)/i', $token)) { $token = '?'; }
            $result .= $token;
        }
        return trim($result);
    }

    protected function persist($row)
    {
        if (!$this->connectionAttempted) {
            $this->connectionAttempted = true;
            $db = $this->ci->db;
            if ($db->dbdriver !== 'mysqli') { return false; }
            $conn = mysqli_init();
            $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 3);
            if (is_array($db->encrypt)) {
                $ssl = $db->encrypt;
                $conn->ssl_set(isset($ssl['ssl_key']) ? $ssl['ssl_key'] : null, isset($ssl['ssl_cert']) ? $ssl['ssl_cert'] : null,
                    isset($ssl['ssl_ca']) ? $ssl['ssl_ca'] : null, isset($ssl['ssl_capath']) ? $ssl['ssl_capath'] : null,
                    isset($ssl['ssl_cipher']) ? $ssl['ssl_cipher'] : null);
            }
            if (!@$conn->real_connect($db->hostname, $db->username, $db->password, $db->database,
                $db->port ?: 3306, null, is_array($db->encrypt) ? MYSQLI_CLIENT_SSL : 0)) { return false; }
            if (!@$conn->set_charset($db->char_set)) { $conn->close(); return false; }
            $this->connection = $conn;
        }
        if (!$this->connection) { return false; }
        $table = $this->ci->db->dbprefix . 'logs';
        if (!preg_match('/^[a-z0-9_]+$/i', $table)) { return false; }
        $fields = array_keys($row);
        $statement = @$this->connection->prepare('INSERT INTO `' . $table . '` (`' . implode('`,`', $fields) . '`) VALUES (' . implode(',', array_fill(0, count($fields), '?')) . ')');
        if (!$statement) { return false; }
        $values = array_values($row);
        $statement->bind_param(str_repeat('s', count($values)), ...$values);
        try { return (bool) @$statement->execute(); }
        finally { $statement->close(); }
    }

    public function __destruct()
    {
        if ($this->connection) { $this->connection->close(); }
    }
}
