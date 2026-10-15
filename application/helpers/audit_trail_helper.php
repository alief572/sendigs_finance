<?php defined('BASEPATH') || exit('No direct script access allowed');

/** Record an activity without changing the business connection or response. */
if (!function_exists('audit_log')) {
    function audit_log($message, $status = null, $context = array())
    {
        try {
            $CI =& get_instance();
            $CI->load->library('Audit_logger');
            return $CI->audit_logger->write($message, $status, $context);
        } catch (Throwable $error) {
            log_message('error', 'Audit Trail: writer unavailable.');
            return false;
        }
    }
}

/** Internal legacy bridge: defer only when an explicitly activated recorder owns the request. */
if (!function_exists('audit_trail_legacy_log')) {
    function audit_trail_legacy_log($message, $status = null, $context = array())
    {
        try {
            $CI =& get_instance();
            if (isset($CI->module_audit_recorder) && $CI->module_audit_recorder->owns_request()) {
                return $CI->module_audit_recorder->defer($message, $status, $context);
            }
            return audit_log($message, $status, $context);
        } catch (Throwable $error) {
            log_message('error', 'Audit Trail: legacy bridge unavailable.');
            return false;
        }
    }
}
