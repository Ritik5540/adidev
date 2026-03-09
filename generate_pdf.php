<?php
// generate_pdf.php
require_once('catalogue.php');

// Use library like DOMPDF or TCPDF to generate PDF
// Example with DOMPDF:
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;

$dompdf = new Dompdf();
$dompdf->loadHtml($catalogue_html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("product_catalogue.pdf", array("Attachment" => 1));
?>