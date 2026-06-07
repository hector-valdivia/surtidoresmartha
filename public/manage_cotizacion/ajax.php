<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
    use Spipu\Html2Pdf\Html2Pdf;

    require(__DIR__ . "/../../vendor/autoload.php");

	session_start();
	require(__DIR__ . "/../../funciones.php");

	// Comprobar si la pagina se cargo con AJAX.
	if ( !isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) != 'xmlhttprequest' ) die( 'No acceda a esta p&aacute;gina directamente desde su navegador.' );

	// Tipo de archivo: JSON.
	header( 'Content-type: application/json' );

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

	//Conexion de BD
	$con = conecta();

	//data al resultado de la operacion
	$data = array();

	//Informacion del usuario que realiza la cotizacion
	$user_logueado = info_personal( desencriptar( limpiar($_SESSION['id']) ) );
	//Informacion de la sucursal del usuario
	$sucursal = sucursal_usuario();

	//Cuerpo del correo
	$cuerpo_correo = '
		<h3>Adjunto cotizacion</h3>
		<p>Cualquier duda o aclaracion quedo a sus ordenes</p>
		<br />
		<p>Gracias y Saludos</p>

		<hr>
		<table>
			<tr>
				<td style="width:200px"><img src="'._BASE_URL.'/assets/img/logopdf.jpg" style="width:90%" ></td>
				<td sryle="text-align: left;">
					'.nombre_personal($user_logueado->id_usuario).'<br />
					http://surtidoresmartha.com
				</td>
			</tr>
		</table>
	';

	//Pasar post a nombres
	if( $_POST ){
		$keys_post = array_keys($_POST);
		foreach ($keys_post as $key_post){
			$$key_post = limpiar($_POST[$key_post]);
		}
	}

	if ( !isset($hacer) ) {
		$hacer = '';
	}

	if ( !isset($total_neto) ) {
		$total_neto = 0;
	}

	switch ( $hacer ) {

		case 'guardar_enviar':
			try {
				$id_cotizacion = 'SIM-'.date('dms').'-'.date('Y');
				$datos = datosCotizacion($total_neto);
				$row = conceptosCotizacion();

				guardarCotizacion($con, $id_cotizacion, $user_logueado, $sucursal, $datos, $row, true);
				$archivo = generarPdfCotizacion($con, $id_cotizacion, $user_logueado, $sucursal, $datos, $row);
				enviarCotizacion($archivo, $datos['email_cliente'], $datos['email_envio'], $datos['asunto'], $cuerpo_correo);
				if (file_exists($archivo)) {
					unlink($archivo);
				}

				$data = array(
					'r' => 1,
					'mensaje' => 'Se guardo y se envio la cotizacion',
					'id_cotizacion' => $id_cotizacion
				);
			}catch (Exception $e){
				$data = respuestaError($e);
			}
		break;

		case 'guardar':
			try {
				$id_cotizacion = 'SIM-'.date('dms').'-'.date('Y');
				$datos = datosCotizacion($total_neto);
				$row = conceptosCotizacion();

				guardarCotizacion($con, $id_cotizacion, $user_logueado, $sucursal, $datos, $row, true);

				$data = array(
					'r' => 1,
					'mensaje' => 'Se guardo la cotización',
					'id_cotizacion' => $id_cotizacion
				);
			}catch (Exception $e){
				$data = respuestaError($e);
			}
		break;

		case 'pdf_guardar':
			try {
				$datos = datosCotizacion($total_neto);
				$row = conceptosCotizacion();

				guardarCotizacion($con, $id_cotizacion, $user_logueado, $sucursal, $datos, $row, false);
				$archivo = generarPdfCotizacion($con, $id_cotizacion, $user_logueado, $sucursal, $datos, $row);

				$data = array(
					'r' => 1,
					'mensaje' => 'Se genero el PDF de la Cotización',
					'archivo' => $archivo
				);
			}catch (Exception $e){
				$data = respuestaError($e);
			}
		break;

		case 'editar_enviar':
			try {
				$datos = datosCotizacion($total_neto);
				$row = conceptosCotizacion();

				guardarCotizacion($con, $id_cotizacion, $user_logueado, $sucursal, $datos, $row, false);
				$archivo = generarPdfCotizacion($con, $id_cotizacion, $user_logueado, $sucursal, $datos, $row);
				enviarCotizacion($archivo, $datos['email_cliente'], $datos['email_envio'], $datos['asunto'], $cuerpo_correo);
				if (file_exists($archivo)) {
					unlink($archivo);
				}

				$data = array(
					'r' => 1,
					'mensaje' => 'Se guardo y se envio la cotizacion',
					'id_cotizacion' => $id_cotizacion
				);
			}catch (Exception $e){
				$data = respuestaError($e);
			}
		break;

		case 'editar':
			try {
				$datos = datosCotizacion($total_neto);
				$row = conceptosCotizacion();

				guardarCotizacion($con, $id_cotizacion, $user_logueado, $sucursal, $datos, $row, false);

				$data = array(
					'r' => 1,
					'mensaje' => 'Se guardo la cotizacion',
					'id_cotizacion' => $id_cotizacion
				);
			}catch (Exception $e){
				$data = respuestaError($e);
			}
		break;

		case 'borrar':
			try {
				$id_cotizacion = desencriptar($id_cotizacion);
				$d = $con->prepare("DELETE FROM aio_cotizacion WHERE id_cotizacion=:id_cotizacion");
				$d->bindParam(':id_cotizacion',$id_cotizacion);
				$d->execute();

				$d = $con->prepare("DELETE FROM aio_cotizacion_conceptos WHERE id_cotizacion=:id_cotizacion");
				$d->bindParam(':id_cotizacion',$id_cotizacion);
				$d->execute();

				$data = array(
					'r' => 1,
					'mensaje' => 'Se elimino la cotizacion'
				);
			}catch (Exception $e){
				$data = respuestaError($e);
			}
		break;

		default:
			$data = array(
				'r' => 0,
				'mensaje' => 'No juegues conmigo'
			);
		break;
	}

	//data al resultado
	echo json_encode($data);

	$con = null;
