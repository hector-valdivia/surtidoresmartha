<?php
	session_start();
	require(__DIR__ . "/../../funciones.php");

	// Comprobar si la página se cargo con AJAX.
	if ( strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) != 'xmlhttprequest' ) die( 'No acceda a esta p&aacute;gina directamente desde su navegador.' );

	// Tipo de archivo: JSON.
	header( 'Content-type: application/json' );

	//Conexion de BD
	$con = conecta();
	$ext = conecta_extrangero();

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

	switch ( $_GET['action'] ) {

		case 'reporte':
			// get the HTML
			ob_start();
			include('reporte.php');
			$html = ob_get_clean();
			
			require_once(__DIR__ . "/../../functions/html2pdf/html2pdf.class.php");

			try{
				$archivo = 'reporte.pdf';
				$html2pdf = new HTML2PDF('P','LETTER','es',array('mL', 'mT', 'mR', 'mB'));
				$html2pdf->pdf->SetDisplayMode('fullpage');
				$html2pdf->pdf->SetAuthor('Surtidores Martha');
				$html2pdf->WriteHTML($html);
				$html2pdf->setDefaultFont('helvetica');	
				$html2pdf->Output($archivo,'F');

				$data = array(
					'r' 	  => 1,
					'mensaje' => 'Se genero el PDF de la Cotización',
					'archivo' => $archivo
				);
			}catch(HTML2PDF_exception $e) {
				$data = array(
					'r' 	  => 0,
					'mensaje' => $e->getMessage()
				);
			}
		break;

		default:
			$data = array(
				'r' 	  => 0,
				'mensaje' => 'No juegues conmigo'
			);
		break;
	}

	//data al resultado
	echo json_encode($data);

	$con = null;
?>