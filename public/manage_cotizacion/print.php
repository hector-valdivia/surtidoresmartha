<?php

require(__DIR__ . "/../../vendor/autoload.php");

session_start();
require(__DIR__ . "/../../funciones.php");
require_once "cotizacion_funciones.php";

$con = conecta();
registrado();

$id = isset($_GET['id']) ? desencriptar($_GET['id']) : '';
if (empty($id)) {
    header('Location: table.php');
    exit;
}

$b = $con->prepare("SELECT * FROM aio_cotizacion WHERE id=:id LIMIT 1");
$b->bindParam(':id', $id);
$b->execute();
$cot = $b->fetchObject();

if (!$cot) {
    header('Location: table.php');
    exit;
}

$user_logueado = info_personal($cot->id_usuario_creo);
$sucursal = sucursal_usuario();
$datos = array(
    'para' => $cot->para,
    'atencion' => $cot->atencion,
    'asunto' => $cot->asunto,
    'nota' => $cot->nota,
);

$row = array();
$q = $con->prepare("SELECT descripcion, cantidad, unidad, pu, costo FROM aio_cotizacion_conceptos WHERE id_cotizacion=:id_cotizacion");
$q->bindParam(':id_cotizacion', $cot->id_cotizacion);
$q->execute();

while ($r = $q->fetch(PDO::FETCH_ASSOC)) {
    $row[] = $r;
}

viewPdfCotizacion($con, $cot->id_cotizacion, $user_logueado, $sucursal, $datos, $row);
