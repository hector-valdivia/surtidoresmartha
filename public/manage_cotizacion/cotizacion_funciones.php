<?php

require(__DIR__ . "/../../vendor/autoload.php");
use Spipu\Html2Pdf\Html2Pdf;

function postCotizacion($key)
{
    return isset($_POST[$key]) ? limpiar($_POST[$key]) : '';
}

function datosCotizacion($total_neto)
{
    return [
        'para' => postCotizacion('para'),
        'asunto' => postCotizacion('asunto_correo'),
        'email_envio' => postCotizacion('mi_email'),
        'email_cliente' => postCotizacion('cliente_email'),
        'fecha' => date('Y-m-d'),
        'hora' => date('H:i:s'),
        'nota' => base64_encode(isset($_POST['nota']) ? $_POST['nota'] : ''),
        'atencion' => postCotizacion('atencion'),
        'total' => cleanNumber($total_neto),
    ];
}

function conceptosCotizacion()
{
    $tabla = json_decode(stripslashes(isset($_GET['action']) ? $_GET['action'] : ''), true);
    if ( empty($tabla) || count($tabla) == 0 ) {
        throw new Exception("No se agrego ningún concepto a la cotización");
    }

    $row = array();
    for ($i = 1, $j = 0; $i < count($tabla) - 1; $i++, $j++) {
        $row[$j]['descripcion'] = $tabla[$i]['descripcion'];
        $row[$j]['cantidad'] = cleanNumber($tabla[$i]['cantidad']);
        $row[$j]['unidad'] = $tabla[$i]['unidad'];
        $row[$j]['pu'] = cleanNumber($tabla[$i]['pu']);
        $row[$j]['costo'] = cleanNumber($tabla[$i]['costo']);
    }

    if ( empty($row) ) {
        throw new Exception("No se agrego ningún concepto a la cotización");
    }

    return $row;
}

function totalConceptosCotizacion($row)
{
    $total = 0;

    foreach ($row as $fila) {
        $total += cleanNumber($fila['costo']);
    }

    return $total;
}

function insertarCotizacion($con, $id_cotizacion, $user_logueado, $sucursal, $datos)
{
    $i = $con->prepare("INSERT INTO aio_cotizacion
			(id_cotizacion, id_usuario_creo, para, asunto, email_envio, email_cliente, fecha, hora, nota, atencion, total, id_sucursal)
			VALUES
			(:id_cotizacion, :id_usuario_creo, :para, :asunto, :email_envio, :email_cliente, :fecha, :hora, :nota, :atencion, :total, :id_sucursal)
		");
    $i->execute(array(
        ':id_cotizacion' => $id_cotizacion,
        ':id_usuario_creo' => $user_logueado->id_usuario,
        ':para' => $datos['para'],
        ':asunto' => $datos['asunto'],
        ':email_envio' => $datos['email_envio'],
        ':email_cliente' => $datos['email_cliente'],
        ':fecha' => $datos['fecha'],
        ':hora' => $datos['hora'],
        ':nota' => $datos['nota'],
        ':atencion' => $datos['atencion'],
        ':total' => $datos['total'],
        ':id_sucursal' => $sucursal->id_sucursal,
    ));
}

function actualizarCotizacion($con, $id_cotizacion, $datos)
{
    $u = $con->prepare("UPDATE aio_cotizacion
			SET para=:para, asunto=:asunto, email_envio=:email_envio, email_cliente=:email_cliente, fecha=:fecha, hora=:hora, nota=:nota, atencion=:atencion, total=:total
			WHERE id_cotizacion=:id_cotizacion
		");
    $u->execute(array(
        ':para' => $datos['para'],
        ':asunto' => $datos['asunto'],
        ':email_envio' => $datos['email_envio'],
        ':email_cliente' => $datos['email_cliente'],
        ':fecha' => $datos['fecha'],
        ':hora' => $datos['hora'],
        ':nota' => $datos['nota'],
        ':atencion' => $datos['atencion'],
        ':total' => $datos['total'],
        ':id_cotizacion' => $id_cotizacion,
    ));
}

function reemplazarConceptosCotizacion($con, $id_cotizacion, $row)
{
    $d = $con->prepare("DELETE FROM aio_cotizacion_conceptos WHERE id_cotizacion=:id_cotizacion");
    $d->execute(array(':id_cotizacion' => $id_cotizacion));

    insertarConceptosCotizacion($con, $id_cotizacion, $row);
}

function insertarConceptosCotizacion($con, $id_cotizacion, $row)
{
    $i = $con->prepare("INSERT INTO aio_cotizacion_conceptos
			(id_cotizacion, descripcion, cantidad, unidad, pu, costo)
			VALUES
			(:id_cotizacion, :descripcion, :cantidad, :unidad, :pu, :costo)
		");

    foreach ($row as $fila) {
        $i->execute(array(
            ':id_cotizacion' => $id_cotizacion,
            ':descripcion' => $fila['descripcion'],
            ':cantidad' => $fila['cantidad'],
            ':unidad' => $fila['unidad'],
            ':pu' => $fila['pu'],
            ':costo' => $fila['costo'],
        ));
    }
}

function guardarCotizacion($con, $id_cotizacion, $user_logueado, $sucursal, $datos, $row, $esNueva)
{
    $datos['total'] = totalConceptosCotizacion($row);

    if ($esNueva) {
        insertarCotizacion($con, $id_cotizacion, $user_logueado, $sucursal, $datos);
        insertarConceptosCotizacion($con, $id_cotizacion, $row);
        return;
    }

    actualizarCotizacion($con, $id_cotizacion, $datos);
    reemplazarConceptosCotizacion($con, $id_cotizacion, $row);
}

function generarPdfCotizacion($con, $id_cotizacion, $user_logueado, $sucursal, $datos, $row)
{
    $para = $datos['para'];
    $atencion = $datos['atencion'];
    $asunto_correo = $datos['asunto'];
    $nota = $datos['nota'];

    ob_start();
    include(__DIR__ . '/cotizacionpdf.php');
    $html = ob_get_clean();

    $archivo = $id_cotizacion . '.pdf';
    $html2pdf = new HTML2PDF('P', 'LETTER', 'es', array('mL', 'mT', 'mR', 'mB'));
    $html2pdf->pdf->SetDisplayMode('fullpage');
    $html2pdf->pdf->SetAuthor('Surtidores Martha');
    $html2pdf->WriteHTML($html);
    $html2pdf->setDefaultFont('helvetica');
    $html2pdf->Output(__DIR__ . '/'. $archivo, 'F');

    return $archivo;
}

function viewPdfCotizacion($con, $id_cotizacion, $user_logueado, $sucursal, $datos, $row)
{
    $para = $datos['para'];
    $atencion = $datos['atencion'];
    $asunto_correo = $datos['asunto'];
    $nota = $datos['nota'];

    ob_start();
    include(__DIR__ . '/cotizacionpdf.php');
    $html = ob_get_clean();

    $archivo = $id_cotizacion . '.pdf';
    $html2pdf = new HTML2PDF('P', 'LETTER', 'es', array('mL', 'mT', 'mR', 'mB'));
    $html2pdf->pdf->SetDisplayMode('fullpage');
    $html2pdf->pdf->SetAuthor('Surtidores Martha');
    $html2pdf->WriteHTML($html);
    $html2pdf->setDefaultFont('helvetica');
    return $html2pdf->Output( $archivo, 'I');
}

function enviarCotizacion($archivo, $cliente_email, $mi_email, $asunto_correo, $cuerpo_correo)
{
    $cc = explode(',', $cliente_email);
    foreach ($cc as $em) {
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = "s89419.gridserver.com";
        $mail->Username = "contacto@surtidoresmartha.com";
        $mail->Password = "SurtMa54321*/";
        $mail->Port = 587;
        $mail->setFrom($mi_email, "Cotizacion surtidoresmartha");
        $mail->addAddress(trim($em));
        $mail->Subject = $asunto_correo;
        $mail->IsHTML(true);
        $mail->Body = $cuerpo_correo;
        $mail->AltBody = 'Adjunto cotizacion Cualquier duda o aclaracion quedo a sus ordenes Gracias y Saludos';
        $mail->addAttachment($archivo, $archivo);
        $mail->send();
    }
}

function respuestaError($e)
{
    return array(
        'r' => 0,
        'mensaje' => $e->getMessage()
    );
}