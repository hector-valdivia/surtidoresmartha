<?php
    require(__DIR__ . "/../../vendor/autoload.php");

	session_start();
	require(__DIR__ . "/../../funciones.php");
    require_once "cotizacion_funciones.php";

	// Comprobar si la pagina se cargo con AJAX.
	if ( !isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) != 'xmlhttprequest' ) die( 'No acceda a esta p&aacute;gina directamente desde su navegador.' );

	// Tipo de archivo: JSON.
	header( 'Content-type: application/json' );

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
