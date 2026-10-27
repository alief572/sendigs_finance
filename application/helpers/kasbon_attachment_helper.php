<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function kasbon_attachment_names($raw)
{
    if ($raw === null || $raw === '') { return array(); }
    if (!is_string($raw)) { throw new RuntimeException('Format lampiran tidak valid'); }
    $raw = trim($raw);
    if ($raw === '') { return array(); }
    if ($raw[0] === '[' || $raw[0] === '{') {
        $names = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($names) || $raw[0] !== '[') {
            throw new RuntimeException('Daftar lampiran rusak atau terpotong');
        }
    } else {
        $names = array($raw);
    }
    foreach ($names as $name) {
        if (!is_string($name) || $name === '' || $name === '.' || $name === '..' ||
            preg_match('/[\\\\\/:\x00-\x1f\x7f]/', $name)) {
            throw new RuntimeException('Nama lampiran tidak valid');
        }
    }
    return array_values(array_unique($names));
}

function kasbon_attachment_path($directory, $name)
{
    kasbon_attachment_names(json_encode(array($name)));
    $base = realpath($directory);
    $path = realpath($directory . '/' . $name);
    if ($base === false || $path === false || !is_file($path) ||
        strncmp($path, $base . DIRECTORY_SEPARATOR, strlen($base) + 1) !== 0) {
        return false;
    }
    return $path;
}

function kasbon_copy_attachments($names, $source, $destination)
{
    $sources = array();
    foreach ($names as $name) {
        $path = kasbon_attachment_path($source, $name);
        if ($path === false || !is_readable($path)) {
            throw new RuntimeException('Lampiran PR tidak ditemukan: ' . $name);
        }
        $sources[$name] = $path;
    }
    if (!$names) { return; }
    $base = realpath($destination);
    if ($base === false || !is_writable($base)) {
        throw new RuntimeException('Gagal menyalin lampiran: ' . reset($names) . ' (direktori tujuan tidak tersedia)');
    }
    $created = array();
    foreach ($sources as $name => $path) {
        $target = $base . DIRECTORY_SEPARATOR . $name;
        // Existing copies belong to earlier kasbon snapshots; do not overwrite them.
        if (kasbon_attachment_path($base, $name) !== false) { continue; }
        if (file_exists($target) || is_link($target)) {
            foreach ($created as $file) { @unlink($file); }
            throw new RuntimeException('Gagal menyalin lampiran: ' . $name);
        }
        if (!@copy($path, $target)) {
            @unlink($target);
            foreach ($created as $file) { @unlink($file); }
            throw new RuntimeException('Gagal menyalin lampiran: ' . $name);
        }
        $created[] = $target;
    }
    return $created;
}

function kasbon_check_attachment_capacity($db, $value)
{
    $fields = $db->field_data('tr_kasbon');
    if ($fields === false) { throw new RuntimeException('Kapasitas kolom lampiran kasbon tidak dapat diperiksa'); }
    foreach ($fields as $field) {
        if ($field->name === 'doc_file' && in_array(strtolower($field->type), array('varchar', 'char'), true) &&
            strlen($value) > $field->max_length) {
            throw new RuntimeException('Daftar lampiran melebihi kapasitas doc_file. Terapkan migration expense 005 sebelum menyimpan kasbon.');
        }
    }
}

function kasbon_pr_document($db, $noPr)
{
    $pr = $db->select('document')->get_where('rutin_non_planning_header', array('no_pr' => $noPr))->row();
    if (!$pr) { throw new RuntimeException('PR Department tidak ditemukan'); }
    return $pr->document;
}

function kasbon_attachment_items($record, $db, $field = 'doc_file', $root = null)
{
    $root = $root === null ? FCPATH : $root;
    $raw = isset($record->$field) ? $record->$field : '';
    $invalid = false;
    try { $names = kasbon_attachment_names($raw); }
    catch (RuntimeException $e) { $names = array(); $invalid = true; }
    $missing = false;
    foreach ($names as $name) {
        if (kasbon_attachment_path($root . '/assets/expense', $name) === false) { $missing = true; }
    }
    $prNames = array();
    if ($field === 'doc_file' && ($invalid || $missing || !$names) &&
        isset($record->tipe_pr, $record->id_pr) && $record->tipe_pr === 'pr departemen') {
        try { $prNames = kasbon_attachment_names(kasbon_pr_document($db, $record->id_pr)); }
        catch (RuntimeException $e) { /* Keep the missing-file indication when the PR cannot recover it. */ }
        $names = array_values(array_unique(array_merge($names, $prNames)));
    }
    $items = array();
    foreach ($names as $name) {
        $path = null;
        if (kasbon_attachment_path($root . '/assets/expense', $name) !== false) {
            $path = 'assets/expense/' . rawurlencode($name);
        } elseif ($field === 'doc_file' && in_array($name, $prNames, true) &&
            kasbon_attachment_path($root . '/assets/pr', $name) !== false) {
            $path = 'assets/pr/' . rawurlencode($name);
        }
        $items[] = array('name' => $name, 'path' => $path);
    }
    if ($invalid && !$items) {
        $items[] = array('name' => 'Daftar lampiran rusak atau terpotong; lampiran PR tidak tersedia', 'path' => null);
    }
    return $items;
}

function kasbon_attachment_html($record, $db, $preview = false, $field = null)
{
    $html = '';
    $seen = array();
    foreach ($field === null ? array('doc_file', 'doc_file_2') : array($field) as $column) {
        foreach (kasbon_attachment_items($record, $db, $column) as $item) {
            if (isset($seen[$item['name']])) { continue; }
            $seen[$item['name']] = true;
            $name = htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8');
            if ($item['path'] === null) {
                $html .= '<p class="text-warning">Lampiran tidak tersedia: ' . $name . '</p>';
                continue;
            }
            $url = htmlspecialchars(base_url($item['path']), ENT_QUOTES, 'UTF-8');
            $html .= '<div class="kasbon-attachment" style="margin-bottom:16px;overflow-wrap:anywhere"><a href="' . $url . '" target="_blank" rel="noopener">' . $name . '</a> <a href="' . $url . '" download>Unduh</a>';
            if ($preview) {
                $ext = strtolower(pathinfo($item['name'], PATHINFO_EXTENSION));
                if ($ext === 'pdf') {
                    $html .= '<div class="pdf-render-container" data-pdf-url="' . $url . '"><iframe src="' . $url . '#toolbar=0&navpanes=0" title="' . $name . '" style="width:100%;height:600px;border:0"></iframe></div>';
                } elseif (in_array($ext, array('png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'), true)) {
                    $html .= '<div><a href="' . $url . '" target="_blank" rel="noopener"><img src="' . $url . '" alt="' . $name . '" class="attachment-img img-responsive" style="max-width:100%;height:auto"></a></div>';
                }
            }
            $html .= '</div>';
        }
    }
    return $html;
}
