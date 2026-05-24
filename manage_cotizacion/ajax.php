<?php
	session_start();
	require("../funciones.php");

	// Comprobar si la página se cargo con AJAX.
	if ( strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) != 'xmlhttprequest' ) die( 'No acceda a esta p&aacute;gina directamente desde su navegador.' );

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

	switch ( $hacer ) {

		case 'guardar_enviar':
			try {
				//ID de la cotizacion
				$id_cotizacion = 'SIM-'.date('dms').'-'.date('Y');
				//Fecha
				$fecha = date('Y-m-d');
				//Hora
				$hora = date('H:i:s');
				//Nota
				$nota = base64_encode($_POST['nota']);

				$i = $con->prepare("INSERT INTO aio_cotizacion 
					(id_cotizacion, id_usuario_creo, para, asunto, email_envio, email_cliente, fecha, hora, nota, atencion, total, id_sucursal)
					VALUES 
					(:id_cotizacion, :id_usuario_creo, :para, :asunto, :email_envio, :email_cliente, :fecha, :hora, :nota, :atencion, :total, :id_sucursal)
				");
				$i->bindParam(':id_cotizacion', $id_cotizacion);
				$i->bindParam(':id_usuario_creo', $user_logueado->id_usuario);
				$i->bindParam(':para', $para);
				$i->bindParam(':asunto', $asunto_correo);
				$i->bindParam(':email_envio', $mi_email);
				$i->bindParam(':email_cliente', $cliente_email);
				$i->bindParam(':fecha', $fecha);
				$i->bindParam(':hora', $hora);
				$i->bindParam(':nota', $nota);
				$i->bindParam(':atencion',$atencion);
				$i->bindParam(':total',$total_neto);
				$i->bindParam(':id_sucursal', $sucursal->id_sucursal);
				$i->execute();

				//Toda la informacion de la tabla de cotizacion en JSON
				$tabla = json_decode(stripslashes($_GET['action']),true);
				if ( empty($tabla) || count($tabla) == 0 ) throw new Exception("No se agrego ningún concepto a la cotización");
				//Variable para las filas
				$row = array();
				//Generar las filas y acomodar la informacion
				for ($i=1, $j=0; $i < count($tabla)-1 ; $i++, $j++) {
					$row[$j]['descripcion'] = $tabla[$i]['descripcion'];
					$row[$j]['cantidad'] 	= $tabla[$i]['cantidad'];
					$row[$j]['unidad'] 		= str_replace('$', '', $tabla[$i]['unidad']);
					$row[$j]['pu']			= str_replace('$', '', $tabla[$i]['pu']);
					$row[$j]['costo'] 		= str_replace('$', '', $tabla[$i]['costo']);
				};

				foreach ($row as $fila) {
					$i = $con->prepare("INSERT INTO aio_cotizacion_conceptos 
						(id_cotizacion, descripcion, cantidad, unidad, pu, costo)
						VALUES
						(:id_cotizacion, :descripcion, :cantidad, :unidad, :pu, :costo)
					");
					$i->bindParam(':id_cotizacion', $id_cotizacion);
					$i->bindParam(':descripcion', $fila['descripcion']);
					$i->bindParam(':cantidad', $fila['cantidad']);
					$i->bindParam(':unidad', $fila['unidad']);
					$i->bindParam(':pu', $fila['pu']);
					$i->bindParam(':costo',$fila['costo']);
					$i->execute();
				}

				// get the HTML PDF
				ob_start();
				include('cotizacionpdf.php'); //Formato del PDF
				$html = ob_get_clean();

				//Libreria para genera el PDF				
				require_once('../functions/html2pdf/html2pdf.class.php');
				$archivo = $id_cotizacion.'.pdf'; //Nombre del archivo generado con la id de la cotizacion
				$html2pdf = new HTML2PDF('P','LETTER','es',array('mL', 'mT', 'mR', 'mB'));
				$html2pdf->pdf->SetDisplayMode('fullpage');
				$html2pdf->pdf->SetAuthor('Surtidores Martha');
				$html2pdf->WriteHTML($html);
				$html2pdf->setDefaultFont('helvetica');	
				$html2pdf->Output($archivo,'F'); //Se crea el pdf en el servidor

				//Por que lo querian con copia
				$cc = explode(',', $cliente_email);
				//Archivos necesarios para el email
				require_once("../functions/phpmailer/class.phpmailer.php");
				//Ciclos de envio
				foreach ($cc as $em) {
					//Enviar cotizacion					
					$mail = new PHPMailer(); //Generar el objeto de correo
					// Set PHPMailer to use the sendmail transport
					$mail->IsSMTP();
					$mail->SMTPAuth = true;
					$mail->Host = "s89419.gridserver.com"; // A RELLENAR. Aquí pondremos el SMTP a utilizar. Por ej. mail.midominio.com
					$mail->Username = "contacto@surtidoresmartha.com"; // A RELLENAR. Email de la cuenta de correo. ej.info@midominio.com La cuenta de correo debe ser creada previamente. 
					$mail->Password = "SurtMa54321*/"; // A RELLENAR. Aqui pondremos la contraseña de la cuenta de correo
					$mail->Port = 587; // Puerto de conexión al servidor de envio. 
					//Set who the message is to be sent from
					$mail->setFrom($mi_email, "Cotizacion surtidoresmartha");
					//Set who the message is to be sent to
					$mail->addAddress( trim($em) );
					//Set the subject line
					$mail->Subject = $asunto_correo;
					//Activar en el objeto el HTML
					$mail->IsHTML(true);
					//Cuerpo del mensaje
					$mail->Body = $cuerpo_correo;
					//Replace the plain text body with one created manually
					$mail->AltBody = 'Adjunto cotizacion Cualquier duda o aclaracion quedo a sus ordenes Gracias y Saludos';
					//Attach an image file
					$mail->addAttachment($archivo,$archivo);
					$mail->send();
				}

				unlink($archivo);

				$data = array(
					'r' 	  => 1,
					'mensaje' => 'Se guardo y se envio la cotizacion',
					'id_cotizacion' => $id_cotizacion
				);
			}catch (Exception $e){
				$data = array(
					'r' 	  => 0,
					'mensaje' => $e->getMessage()
				);
			}
		break;

		case 'guardar':
			try {
				//ID de la cotizacion
				$id_cotizacion = 'SIM-'.date('dms').'-'.date('Y');
				//Fecha
				$fecha = date('Y-m-d');
				//Hora
				$hora = date('H:i:s');
				//Nota
				$nota = base64_encode($_POST['nota']);

				$i = $con->prepare("INSERT INTO aio_cotizacion 
					(id_cotizacion, id_usuario_creo, para, asunto, email_envio, email_cliente, fecha, hora, nota, atencion, total, id_sucursal)
					VALUES 
					(:id_cotizacion, :id_usuario_creo, :para, :asunto, :email_envio, :email_cliente, :fecha, :hora, :nota, :atencion, :total, :id_sucursal)
				");
				$i->bindParam(':id_cotizacion', $id_cotizacion);
				$i->bindParam(':id_usuario_creo', $user_logueado->id_usuario);
				$i->bindParam(':para', $para);
				$i->bindParam(':asunto', $asunto_correo);
				$i->bindParam(':email_envio', $mi_email);
				$i->bindParam(':email_cliente', $cliente_email);
				$i->bindParam(':fecha', $fecha);
				$i->bindParam(':hora', $hora);
				$i->bindParam(':nota', $nota);
				$i->bindParam(':atencion', $atencion);
				$i->bindParam(':total', $total_neto);
				$i->bindParam(':id_sucursal', $sucursal->id_sucursal);
				$i->execute();

				//Toda la informacion de la tabla de cotizacion en JSON
				$tabla = json_decode(stripslashes($_GET['action']),true);
				if ( empty($tabla) || count($tabla) == 0 ) throw new Exception("No se agrego ningún concepto a la cotización");
				//Variable para las filas
				$row = array();
				//Generar las filas y acomodar la informacion
				for ($i=1, $j=0; $i < count($tabla)-1 ; $i++, $j++) {
					$row[$j]['descripcion'] = $tabla[$i]['descripcion'];
					$row[$j]['cantidad'] 	= $tabla[$i]['cantidad'];
					$row[$j]['unidad'] 		= str_replace('$', '', $tabla[$i]['unidad']);
					$row[$j]['pu']			= str_replace('$', '', $tabla[$i]['pu']);
					$row[$j]['costo'] 		= str_replace('$', '', $tabla[$i]['costo']);
				};

				foreach ($row as $fila) {
					$i = $con->prepare("INSERT INTO aio_cotizacion_conceptos 
						(id_cotizacion, descripcion, cantidad, unidad, pu, costo)
						VALUES
						(:id_cotizacion, :descripcion, :cantidad, :unidad, :pu, :costo)
					");
					$i->bindParam(':id_cotizacion', $id_cotizacion);
					$i->bindParam(':descripcion', $fila['descripcion']);
					$i->bindParam(':cantidad', $fila['cantidad']);
					$i->bindParam(':unidad', $fila['unidad']);
					$i->bindParam(':pu', $fila['pu']);
					$i->bindParam(':costo',$fila['costo']);
					$i->execute();
				}

				$data = array(
					'r' 	  => 1,
					'mensaje' => 'Se guardo la cotización',
					'id_cotizacion' => $id_cotizacion
				);

			}catch (Exception $e){
				$data = array(
					'r' 	  => 0,
					'mensaje' => $e->getMessage()
				);
			}
		break;

		case 'pdf_guardar':
			//Nota
			$nota = base64_encode($_POST['nota']);
			//Fecha
			$fecha = date('Y-m-d');
			//Hora
			$hora = date('H:i:s');

			//Actualizar la informacion general de la cotizacion
			$u = $con->prepare("UPDATE aio_cotizacion 
				SET para=:para, asunto=:asunto, email_envio=:email_envio, email_cliente=:email_cliente, fecha=:fecha, hora=:hora, nota=:nota, atencion=:atencion, total=:total
				WHERE id_cotizacion=:id_cotizacion
			");
			$u->bindParam(':para', $para);
			$u->bindParam(':asunto', $asunto_correo);
			$u->bindParam(':email_envio', $mi_email);
			$u->bindParam(':email_cliente', $cliente_email);
			$u->bindParam(':fecha', $fecha);
			$u->bindParam(':hora', $hora);
			$u->bindParam(':nota', $nota);
			$u->bindParam(':atencion', $atencion);
			$u->bindParam(':id_cotizacion',$id_cotizacion);
			$u->bindParam(':total',$total_neto);
			$u->execute();

			//Borrar toda la informacion de la cotizacion para volverla a escribir
			$d = $con->prepare("DELETE FROM aio_cotizacion_conceptos WHERE id_cotizacion=:id_cotizacion");
			$d->bindParam(':id_cotizacion',$id_cotizacion);
			$d->execute();

			//Toda la informacion de la tabla de cotizacion en JSON
			$tabla = json_decode(stripslashes($_GET['action']),true);
			if ( empty($tabla) || count($tabla) == 0 ) throw new Exception("No se agrego ningún concepto a la cotización");
			//Variable para las filas
			$row = array();
			//Generar las filas y acomodar la informacion
			for ($i=1, $j=0; $i < count($tabla)-1 ; $i++, $j++) {
				$row[$j]['descripcion'] = $tabla[$i]['descripcion'];
				$row[$j]['cantidad'] 	= $tabla[$i]['cantidad'];
				$row[$j]['unidad'] 		= str_replace('$', '', $tabla[$i]['unidad']);
				$row[$j]['pu']			= str_replace('$', '', $tabla[$i]['pu']);
				$row[$j]['costo'] 		= str_replace('$', '', $tabla[$i]['costo']);
			};

			foreach ($row as $fila) {
				$i = $con->prepare("INSERT INTO aio_cotizacion_conceptos 
					(id_cotizacion, descripcion, cantidad, unidad, pu, costo)
					VALUES
					(:id_cotizacion, :descripcion, :cantidad, :unidad, :pu, :costo)
				");
				$i->bindParam(':id_cotizacion', $id_cotizacion);
				$i->bindParam(':descripcion', $fila['descripcion']);
				$i->bindParam(':cantidad', $fila['cantidad']);
				$i->bindParam(':unidad', $fila['unidad']);
				$i->bindParam(':pu', $fila['pu']);
				$i->bindParam(':costo',$fila['costo']);
				$i->execute();
			}
			// get the HTML
			ob_start();
			include('cotizacionpdf.php');
			$html = ob_get_clean();
			
			require_once('../functions/html2pdf/html2pdf.class.php');

			try{
				$archivo = $id_cotizacion.'.pdf';
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

		case 'editar_enviar':
			try {
				//Nota
				$nota = base64_encode($_POST['nota']);
				//Fecha
				$fecha = date('Y-m-d');
				//Hora
				$hora = date('H:i:s');

				$u = $con->prepare("UPDATE aio_cotizacion 
					SET para=:para, asunto=:asunto, email_envio=:email_envio, email_cliente=:email_cliente, fecha=:fecha, hora=:hora, nota=:nota, atencion=:atencion
					WHERE id_cotizacion=:id_cotizacion
				");
				$u->bindParam(':para', $para);
				$u->bindParam(':asunto', $asunto_correo);
				$u->bindParam(':email_envio', $mi_email);
				$u->bindParam(':email_cliente', $cliente_email);
				$u->bindParam(':fecha', $fecha);
				$u->bindParam(':hora', $hora);
				$u->bindParam(':nota', $nota);
				$u->bindParam(':atencion', $atencion);
				$u->bindParam(':id_cotizacion',$id_cotizacion);
				$u->execute();

				$d = $con->prepare("DELETE FROM aio_cotizacion_conceptos WHERE id_cotizacion=:id_cotizacion");
				$d->bindParam(':id_cotizacion',$id_cotizacion);
				$d->execute();

				//Toda la informacion de la tabla de cotizacion en JSON
				$tabla = json_decode(stripslashes($_GET['action']),true);
				if ( empty($tabla) || count($tabla) == 0 ) throw new Exception("No se agrego ningún concepto a la cotización");
				//Variable para las filas
				$row = array();
				//Generar las filas y acomodar la informacion
				for ($i=1, $j=0; $i < count($tabla)-1 ; $i++, $j++) {
					$row[$j]['descripcion'] = $tabla[$i]['descripcion'];
					$row[$j]['cantidad'] 	= $tabla[$i]['cantidad'];
					$row[$j]['unidad'] 		= str_replace('$', '', $tabla[$i]['unidad']);
					$row[$j]['pu']			= str_replace('$', '', $tabla[$i]['pu']);
					$row[$j]['costo'] 		= str_replace('$', '', $tabla[$i]['costo']);
				};

				foreach ($row as $fila) {
					$i = $con->prepare("INSERT INTO aio_cotizacion_conceptos 
						(id_cotizacion, descripcion, cantidad, unidad, pu, costo)
						VALUES
						(:id_cotizacion, :descripcion, :cantidad, :unidad, :pu, :costo)
					");
					$i->bindParam(':id_cotizacion', $id_cotizacion);
					$i->bindParam(':descripcion', $fila['descripcion']);
					$i->bindParam(':cantidad', $fila['cantidad']);
					$i->bindParam(':unidad', $fila['unidad']);
					$i->bindParam(':pu', $fila['pu']);
					$i->bindParam(':costo',$fila['costo']);
					$i->execute();
				}


				// get the HTML PDF
				ob_start();
				include('cotizacionpdf.php'); //Formato del PDF
				$html = ob_get_clean();

				//Libreria para genera el PDF				
				require_once('../functions/html2pdf/html2pdf.class.php');
				$archivo = $id_cotizacion.'.pdf'; //Nombre del archivo generado con la id de la cotizacion
				$html2pdf = new HTML2PDF('P','LETTER','es',array('mL', 'mT', 'mR', 'mB'));
				$html2pdf->pdf->SetDisplayMode('fullpage');
				$html2pdf->pdf->SetAuthor('Surtidores Martha');
				$html2pdf->WriteHTML($html);
				$html2pdf->setDefaultFont('helvetica');	
				$html2pdf->Output($archivo,'F'); //Se crea el pdf en el servidor

				//Por que lo querian con copia
				$cc = explode(',', $cliente_email);
				//Archivos necesarios para el email
				require_once("../functions/phpmailer/class.phpmailer.php");
				//Ciclos de envio
				foreach ($cc as $em) {
					//Enviar cotizacion					
					$mail = new PHPMailer(); //Generar el objeto de correo
					// Set PHPMailer to use the sendmail transport
					$mail->IsSMTP();
					$mail->SMTPAuth = true;
					$mail->Host = "s89419.gridserver.com"; // A RELLENAR. Aquí pondremos el SMTP a utilizar. Por ej. mail.midominio.com
					$mail->Username = "contacto@surtidoresmartha.com"; // A RELLENAR. Email de la cuenta de correo. ej.info@midominio.com La cuenta de correo debe ser creada previamente. 
					$mail->Password = "SurtMa54321*/"; // A RELLENAR. Aqui pondremos la contraseña de la cuenta de correo
					$mail->Port = 587; // Puerto de conexión al servidor de envio. 
					//Set who the message is to be sent from
					$mail->setFrom($mi_email, "Cotizacion surtidoresmartha");
					//Set who the message is to be sent to
					$mail->addAddress( trim($em) );
					//Set the subject line
					$mail->Subject = $asunto_correo;
					//Activar en el objeto el HTML
					$mail->IsHTML(true);
					//Cuerpo del mensaje
					$mail->Body = $cuerpo_correo;
					//Replace the plain text body with one created manually
					$mail->AltBody = 'Adjunto cotizacion Cualquier duda o aclaracion quedo a sus ordenes Gracias y Saludos';
					//Attach an image file
					$mail->addAttachment($archivo,$archivo);
					$mail->send();
				}

				unlink($archivo);

				$data = array(
					'r' 	  => 1,
					'mensaje' => 'Se guardo y se envio la cotizacion'.$content_PDF,
					'id_cotizacion' => $id_cotizacion
				);

			}catch (Exception $e){
				$data = array(
					'r' 	  => 0,
					'mensaje' => $e->getMessage()
				);
			}		
		break;

		case 'editar':
			try {
				//Nota
				$nota = base64_encode($_POST['nota']);
				//Fecha
				$fecha = date('Y-m-d');
				//Hora
				$hora = date('H:i:s');

				$u = $con->prepare("UPDATE aio_cotizacion 
					SET para=:para, asunto=:asunto, email_envio=:email_envio, email_cliente=:email_cliente, fecha=:fecha, hora=:hora, nota=:nota, atencion=:atencion
					WHERE id_cotizacion=:id_cotizacion
				");
				$u->bindParam(':para', $para);
				$u->bindParam(':asunto', $asunto_correo);
				$u->bindParam(':email_envio', $mi_email);
				$u->bindParam(':email_cliente', $cliente_email);
				$u->bindParam(':fecha', $fecha);
				$u->bindParam(':hora', $hora);
				$u->bindParam(':nota', $nota);
				$u->bindParam(':atencion', $atencion);
				$u->bindParam(':id_cotizacion',$id_cotizacion);
				$u->execute();

				$d = $con->prepare("DELETE FROM aio_cotizacion_conceptos WHERE id_cotizacion=:id_cotizacion");
				$d->bindParam(':id_cotizacion',$id_cotizacion);
				$d->execute();

				//Toda la informacion de la tabla de cotizacion en JSON
				$tabla = json_decode(stripslashes($_GET['action']),true);
				if ( empty($tabla) || count($tabla) == 0 ) throw new Exception("No se agrego ningún concepto a la cotización");
				//Variable para las filas
				$row = array();
				//Generar las filas y acomodar la informacion
				for ($i=1, $j=0; $i < count($tabla)-1 ; $i++, $j++) {
					$row[$j]['descripcion'] = $tabla[$i]['descripcion'];
					$row[$j]['cantidad'] 	= $tabla[$i]['cantidad'];
					$row[$j]['unidad'] 		= str_replace('$', '', $tabla[$i]['unidad']);
					$row[$j]['pu']			= str_replace('$', '', $tabla[$i]['pu']);
					$row[$j]['costo'] 		= str_replace('$', '', $tabla[$i]['costo']);
				};

				foreach ($row as $fila) {
					$i = $con->prepare("INSERT INTO aio_cotizacion_conceptos 
						(id_cotizacion, descripcion, cantidad, unidad, pu, costo)
						VALUES
						(:id_cotizacion, :descripcion, :cantidad, :unidad, :pu, :costo)
					");
					$i->bindParam(':id_cotizacion', $id_cotizacion);
					$i->bindParam(':descripcion', $fila['descripcion']);
					$i->bindParam(':cantidad', $fila['cantidad']);
					$i->bindParam(':unidad', $fila['unidad']);
					$i->bindParam(':pu', $fila['pu']);
					$i->bindParam(':costo',$fila['costo']);
					$i->execute();
				}

				$data = array(
					'r' 	  => 1,
					'mensaje' => 'Se guardo la cotizacion',
					'id_cotizacion' => $id_cotizacion
				);

			}catch (Exception $e){
				$data = array(
					'r' 	  => 0,
					'mensaje' => $e->getMessage()
				);
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
					'r' 	  => 1,
					'mensaje' => 'Se elimino la cotizacion'
				);
			}catch (Exception $e){
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