<?php

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

class Mpdf extends \Mpdf\Mpdf
{
    public function __construct($mode = '', $format = 'A4', $fontSize = 0, $font = '',
        $left = 15, $right = 15, $top = 16, $bottom = 16, $header = 9, $footer = 9, $orientation = 'P')
    {
        if (is_array($mode)) {
            $config = $mode;
        } else {
            $config = [
                'mode' => $mode,
                'format' => $format ?: 'A4',
                'default_font_size' => (float) $fontSize,
                'default_font' => $font,
                'margin_left' => $left === '' ? 15 : (float) $left,
                'margin_right' => $right === '' ? 15 : (float) $right,
                'margin_top' => $top === '' ? 16 : (float) $top,
                'margin_bottom' => $bottom === '' ? 16 : (float) $bottom,
                'margin_header' => $header === '' ? 9 : (float) $header,
                'margin_footer' => $footer === '' ? 9 : (float) $footer,
                'orientation' => $orientation ?: 'P',
            ];
        }
        $config += ['tempDir' => dirname(__DIR__) . '/cache/mpdf'];
        parent::__construct($config);
    }

    public function SetImportUse(): void
    {
        // PDF import is always enabled by FPDI in mPDF 8.
    }
}
