<?php

// Legacy print views include this path and call new mPDF(). mPDF 6 does not parse on PHP 8,
// so the path now loads the mPDF 8 adapter (class names are case-insensitive).
require_once dirname(__DIR__) . '/Mpdf.php';
