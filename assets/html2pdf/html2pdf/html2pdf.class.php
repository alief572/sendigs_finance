<?php

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

if (!class_exists('HTML2PDF', false)) {
    class_alias(Spipu\Html2Pdf\Html2Pdf::class, 'HTML2PDF');
    class_alias(Spipu\Html2Pdf\Exception\Html2PdfException::class, 'HTML2PDF_exception');
}
