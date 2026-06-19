<?php

session_start();
require(__DIR__ . "/../../vendor/autoload.php");
use Spipu\Html2Pdf\Html2Pdf;
require(__DIR__ . "/../../funciones.php");


registrado();
$con = conecta();

$id_requisicion = isset($_GET['id']) ? desencriptar($_GET['id']) : '';
if (empty($id_requisicion)) {
	header('Location: table.php');
	exit;
}

$b = $con->prepare("SELECT * FROM aio_requisicion WHERE id=:id LIMIT 1");
$b->bindParam(':id', $id_requisicion);
$b->execute();
$requi = $b->fetchObject();

if (!$requi) {
	header('Location: table.php');
	exit;
}

$nota = '';

ob_start();
include(__DIR__ . '/requisicionpdf.php');
$html = ob_get_clean();

$archivo = 'Requisicion-' . str_pad($id_requisicion, 4, '0', STR_PAD_LEFT) . '.pdf';
$html2pdf = new HTML2PDF('P', 'LETTER', 'es', array('mL', 'mT', 'mR', 'mB'));
$html2pdf->pdf->SetDisplayMode('fullpage');
$html2pdf->pdf->SetAuthor('Surtidores Martha');
$html2pdf->WriteHTML($html);
$html2pdf->setDefaultFont('helvetica');
$html2pdf->Output($archivo, 'I');
