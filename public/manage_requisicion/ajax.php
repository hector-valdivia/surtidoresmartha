<?php	
	/*//Enviar cotizacion					
	$mail = new PHPMailer(); //Generar el objeto de correo
	// Set PHPMailer to use the sendmail transport
	$mail->IsSMTP();
	$mail->SMTPAuth = true;
	$mail->Host = "s89419.gridserver.com"; // A RELLENAR. Aquí pondremos el SMTP a utilizar. Por ej. mail.midominio.com
	$mail->Username = "contacto@surtidoresmartha.com"; // A RELLENAR. Email de la cuenta de correo. ej.info@midominio.com La cuenta de correo debe ser creada previamente. 
	$mail->Password = "/"; // A RELLENAR. Aqui pondremos la contraseña de la cuenta de correo
	$mail->Port = 587; // Puerto de conexión al servidor de envio. 

	//Set who the message is to be sent from
	$mail->setFrom("no-replay@surtidoresmartha.com", "Reporte Surtidores Martha");
	//Set who the message is to be sent to
	$mail->addAddress( trim("contacto@surtidoresmartha.com") );
	$mail->addAddress( trim("hect.valdivia@gmail.com") );
	//Set the subject line
	$mail->Subject = "Prueba surtidoresmartha";
	//Activar en el objeto el HTML
	$mail->IsHTML(true);
	$mail->SMTPDebug = 1;
	//Cuerpo del correo
	$cuerpo_correo = '
		<h3>Prueba</h3>
		<p>Solo una prueba</p>
		<br />
		<hr>
	';
	//Cuerpo del mensaje
	$mail->Body = $cuerpo_correo;
	//Replace the plain text body with one created manually
	$mail->AltBody = 'Probando';
	if( $mail->Send() ){
		$arrResult['response'] = 'success';
	} else {
		$arrResult['response'] = 'error';
		echo "There was a problem sending the form.: " . $mail->ErrorInfo;
		exit;
	}
	*/
	session_start();
	require(__DIR__ . "/../../funciones.php");
	require_once(__DIR__ . "/../../functions/phpmailer/class.phpmailer.php");

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

	//Cuerpo del correo
	$cuerpo_correo = '
		<h3>Requisicion</h3>
		<p>Ahi una requisicion nueva en el sistema</p>
		<br />
		<hr>
		<table>
			<tr>
				<td style="width:200px"><img src="'._BASE_URL.'/assets/img/logopdf.jpg" style="width:90%" ></td>
				<td sryle="text-align: left;">
					Sistema Surtidores Martha<br />
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

		case 'guardar':
			try {
				//Fecha de creacion de la requisicion
				$fecha_creacion = date('Y-m-d');
				//Nota
				$motivo = base64_encode($_POST['motivo']);

				$con->beginTransaction();
				//Insercion de la base de datos de la requisicion
				$i = $con->prepare("INSERT INTO aio_requisicion 
					(id_sucursal, descripcion,prioridad, solicita, motivo, fecha, status, bloqueada,fecha_creacion)
					VALUES 
					(:id_sucursal, :descripcion,:prioridad, :solicita, :motivo, :fecha, 'espera', 'no',:fecha_creacion)
				");
				$i->bindParam(':id_sucursal', $sucursal);
				$i->bindParam(':descripcion',$descripcion);
				$i->bindParam(':prioridad', $prioridad);
				$i->bindParam(':solicita', $solicita);
				$i->bindParam(':motivo', $motivo);
				$i->bindParam(':fecha', $fecha);
				$i->bindParam(':fecha_creacion',$fecha_creacion);
				$i->execute();
				//Obtener el id del ultimo valor instertado
				$id_requisicion = $con->lastInsertId();
				//Cerrar el cursor para preparar la siguiente ejecusion
				$i->closeCursor();

				//Se prepara la ejecusion
				$i = $con->prepare("INSERT INTO aio_requisicion_material
					(id_requisicion, material, cantidad, unidad, precio_unitario, iva, proveedor, direccion, status, telefono, email)
					VALUES
					(:id_requisicion, :material, :cantidad, :unidad, :precio_unitario, :iva, :proveedor, :direccion, 'espera', :telefono, :email)
				");
				//Se manda de manera serializada las cotizaciones creadas				
				foreach ($req as $requi) {
					$requi = json_decode( stripslashes($requi) );
					$i->bindParam(':id_requisicion', $id_requisicion);
					$i->bindParam(':material',$requi->material);
					$i->bindParam(':cantidad',$requi->cantidad);
					$i->bindParam(':unidad',$requi->unidad);
					$i->bindParam(':precio_unitario',$requi->precio_unitario);
					$i->bindParam(':iva',$requi->iva);
					$i->bindParam(':proveedor',$requi->proveedor);
					$i->bindParam(':direccion',$requi->direccion);
					$i->bindParam(':telefono',$requi->telefono);
					$i->bindParam(':email',$requi->email);
					$i->execute();
				}
				//Realizamos la escritura
				$con->commit();

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
				$mail->setFrom("no-replay@surtidoresmartha.com", "Reporte Surtidores Martha");
				//Set who the message is to be sent to
				$mail->addAddress( trim("contacto@surtidoresmartha.com") );
				$mail->addAddress( trim("hect.valdivia@gmail.com") );
				//Set the subject line
				$mail->Subject = "Requisicion surtidoresmartha";
				//Activar en el objeto el HTML
				$mail->IsHTML(true);
				//Cuerpo del correo
				$cuerpo_correo = '
					<h3>Requisicion</h3>
					<p>Ahi una requisicion nueva en el sistema <a href="'._BASE_URL.'/manage_requisicion/agregar.php?id='.encriptar($id_requisicion).'">Ir a la requisicion</a></p>
					<br />
					<hr>
					<table>
						<tr>
							<td style="width:200px"><img src="'._BASE_URL.'/assets/img/logopdf.jpg" style="width:90%" ></td>
							<td sryle="text-align: left;">
								Sistema Surtidores Martha<br />
								http://surtidoresmartha.com
							</td>
						</tr>
					</table>
				';
				//Cuerpo del mensaje
				$mail->Body = $cuerpo_correo;
				//Replace the plain text body with one created manually
				$mail->AltBody = 'Se creo una nueva requisicion '._BASE_URL.'/manage_requisicion/agregar.php?id='.encriptar($id_requisicion);
				$mail->send();

				//Exito
				$data = array(
					'r' 		=> 1,
					'mensaje' 	=> 'Se guardo la requisicion',
					'hacer'	  	=> 'editar'
				);

			}catch (Exception $e){
				try { $con->rollBack(); } catch (Exception $e2) {}
				$data = array(
					'r' 	  	=> 0,
					'mensaje' 	=> $e->getMessage(),
					'hacer'   	=> 'guardar'
				);
			}
		break;

		case 'editar':
			try {
				$b = $con->prepare("SELECT COUNT(id) FROM aio_requisicion WHERE id=:id");
				$b->bindParam(':id',$id_requisicion);
				$b->execute();
				if ($b->fetchColumn() == 0 ) throw new Exception("No existe la requisicion");

				//Nota
				$motivo = base64_encode($_POST['motivo']);

				$con->beginTransaction();
				//Insercion de la base de datos de la requisicion
				$u = $con->prepare("UPDATE aio_requisicion SET
					id_sucursal=:id_sucursal, descripcion=:descripcion,prioridad=:prioridad, solicita=:solicita, motivo=:motivo, fecha=:fecha
					WHERE
					id=:id
				");
				$u->bindParam(':id_sucursal', $sucursal);
				$u->bindParam(':descripcion',$descripcion);
				$u->bindParam(':prioridad', $prioridad);
				$u->bindParam(':solicita', $solicita);
				$u->bindParam(':motivo', $motivo);
				$u->bindParam(':fecha', $fecha);
				$u->bindParam(':id', $id_requisicion);
				$u->execute();
				//Cerrar el cursor para preparar la siguiente ejecusion
				$u->closeCursor();
				//Elimanar el material de la requisicion
				$d = $con->prepare("DELETE FROM aio_requisicion_material WHERE id_requisicion=:id");
				$d->bindParam(':id',$id_requisicion);
				$d->execute();
				//Borrar el material de la requisicion
				$d->closeCursor();
				//Se prepara la ejecusion
				$i = $con->prepare("INSERT INTO aio_requisicion_material
					(id_requisicion, material, cantidad, unidad, precio_unitario, iva, proveedor, direccion, status, telefono, email)
					VALUES
					(:id_requisicion, :material, :cantidad, :unidad, :precio_unitario, :iva, :proveedor, :direccion, 'espera', :telefono, :email)
				");
				//Se manda de manera serializada las cotizaciones creadas				
				foreach ($req as $requi) {
					$requi = json_decode( stripslashes($requi) );
					$i->bindParam(':id_requisicion', $id_requisicion);
					$i->bindParam(':material',$requi->material);
					$i->bindParam(':cantidad',$requi->cantidad);
					$i->bindParam(':unidad',$requi->unidad);
					$i->bindParam(':precio_unitario',$requi->precio_unitario);
					$i->bindParam(':iva',$requi->iva);
					$i->bindParam(':proveedor',$requi->proveedor);
					$i->bindParam(':direccion',$requi->direccion);
					$i->bindParam(':telefono',$requi->telefono);
					$i->bindParam(':email',$requi->email);
					$i->execute();
				}
				//Realizamos la escritura
				$con->commit();
				//Exito
				$data = array(
					'r' 	   		 => 1,
					'mensaje' 		 => 'Se actualizo la requisicion',
					'id_requisicion' => $id_requisicion,
					'hacer' 		 => 'editar'
				);

			}catch (Exception $e){
				try { $con->rollBack(); } catch (Exception $e2) {}
				$data = array(
					'r' 	  		 => 0,
					'mensaje' 		 => $e->getMessage(),
					'id_requisicion' => $id_requisicion,
					'hacer'			 => 'editar'
				);
			}	
		break;

		case 'desbloquear':
			try{
				$con->beginTransaction();
				//Insercion de la base de datos de la requisicion
				$u = $con->prepare("UPDATE aio_requisicion SET bloqueada='no' WHERE id=:id");
				$u->bindParam(':id', $id_requisicion);
				$u->execute();
				//Realizamos la escritura
				$con->commit();
				//Exito
				$data = array(
					'r' 	   	 		=> 1,
					'mensaje' 	 		=> 'Se actualizo la requisicion',
					'id_requisicion' 	=> encriptar($id_requisicion),
					'hacer' 		 	=> 'desbloquear'
				);

			}catch (Exception $e){
				try { $con->rollBack(); } catch (Exception $e2) {}
				$data = array(
					'r' 	  	=> 0,
					'mensaje' 	=> $e->getMessage(),
					'id_requisicion' 	=> $id_requisicion,
					'hacer'		=> 'editar_status'
				);
			}
		break;

		case 'bloquear':
		case 'editar_status':
			try {
				//Comprobar que existe la requisicion
				$b = $con->prepare("SELECT COUNT(id) FROM aio_requisicion WHERE id=:id");
				$b->bindParam(':id',$id_requisicion);
				$b->execute();

				//Si no existe la requisicion marcar error
				if ($b->fetchColumn() == 0 ) throw new Exception("No existe la requisicion");


				//Dependiendo si se preciono el boton de bloquear requisicion
				if ( $hacer == 'bloquear' && $status_requi != 'espera'   )  $bloquear = 'si';
				else $bloquear = 'no';
				//Nota
				$motivo = base64_encode($_POST['motivo']);
				//Empezar con la transaccion
				$con->beginTransaction();
				//Insercion de la base de datos de la requisicion
				$u = $con->prepare("UPDATE aio_requisicion SET
					id_sucursal=:id_sucursal, descripcion=:descripcion,prioridad=:prioridad, solicita=:solicita, motivo=:motivo, fecha=:fecha, status=:status, bloqueada=:bloqueada
					WHERE
					id=:id
				");
				$u->bindParam(':id_sucursal', $sucursal);
				$u->bindParam(':descripcion',$descripcion);
				$u->bindParam(':prioridad', $prioridad);
				$u->bindParam(':solicita', $solicita);
				$u->bindParam(':motivo', $motivo);
				$u->bindParam(':fecha', $fecha);
				$u->bindParam(':bloqueada', $bloquear);
				$u->bindParam(':status', $status_requi);
				$u->bindParam(':id', $id_requisicion);
				$u->execute();
				//Cerrar el cursor para preparar la siguiente ejecusion
				$u->closeCursor();
				//Elimanar el material de la requisicion
				$d = $con->prepare("DELETE FROM aio_requisicion_material WHERE id_requisicion=:id");
				$d->bindParam(':id',$id_requisicion);
				$d->execute();
				//Borrar el material de la requisicion
				$d->closeCursor();
				//Se prepara la ejecusion
				$i = $con->prepare("INSERT INTO aio_requisicion_material
					(id_requisicion, material, cantidad, unidad, precio_unitario, proveedor, direccion, status, telefono, email, iva)
					VALUES
					(:id_requisicion, :material, :cantidad, :unidad, :precio_unitario, :proveedor, :direccion, :status, :telefono, :email, :iva)
				");
				//Se manda de manera serializada las cotizaciones creadas
				$s = 0;
				foreach ($req as $requi) {
					$requi = json_decode( stripslashes($requi) );
					$i->bindParam(':id_requisicion', $id_requisicion);
					$i->bindParam(':material',$requi->material);
					$i->bindParam(':cantidad',$requi->cantidad);
					$i->bindParam(':unidad',$requi->unidad);
					$i->bindParam(':precio_unitario',$requi->precio_unitario);
					$i->bindParam(':proveedor',$requi->proveedor);
					$i->bindParam(':direccion',$requi->direccion);
					$i->bindParam(':status',$status[$s]);
					$i->bindParam(':telefono',$requi->telefono);
					$i->bindParam(':email',$requi->email);
					$i->bindParam(':iva',$requi->iva);
					$i->execute();
					$s++;
				}
				//Realizamos la escritura
				$con->commit();
				//Exito
				if ( $hacer == 'bloquear' & $status_requi != 'espera' ) {
					$data = array(
						'r' 	   	 => 1,
						'mensaje' 	 => 'Se actualizo la requisicion',
						'id_requisicion' 	 => encriptar($id_requisicion),
						'hacer' 		 => 'bloquear'
					);
				}else{
					if ( $hacer == 'bloquear' && $status_requi == 'espera') $extra = ', <b>pero no se bloqueo ya que sigue la requisicion en espera.</b>';
					else $extra = '';
					$data = array(
						'r' 	   	 => 1,
						'mensaje' 	 => 'Se actualizo la requisicion'.$extra,
						'id_requisicion' 	 => $id_requisicion,
						'hacer' 		 => 'editar_status'
					);
				}

			}catch (Exception $e){
				try { $con->rollBack(); } catch (Exception $e2) {}
				$data = array(
					'r' 	  	=> 0,
					'mensaje' 	=> $e->getMessage(),
					'id_requisicion' 	=> $id_requisicion,
					'hacer'		=> 'editar_status'
				);
			}	
		break;

		case 'activar_pdf':
		case 'desactivar_pdf':
			try {
				//Comprobar que sean los usuarios maestros
				if ( 96140 != $user_logueado->id_usuario && 12345 != $user_logueado->id_usuario && 86261 != $user_logueado->id_usuario ) throw new Exception("No es un usuario autorizado");
				
				//Password enviado
				$password = encriptar($password);

				//Empezar con la transaccion
				$con->beginTransaction();

				//Comprobar contraseña del usuario
			 	$b_pass = $con->prepare("SELECT count(password) FROM aio_personal WHERE password=:pass AND id_usuario=:id LIMIT 1");
			 	$b_pass->bindParam(':id',$user_logueado->id_usuario);
			 	$b_pass->bindParam(':pass',$password);
			 	$b_pass->execute();
			 	if ( $b_pass->fetchColumn() == 0 ) throw new Exception("Error de contraseña y usuario");

			 	//Buscar existencia de la requisicion
				$b = $con->prepare("SELECT COUNT(id) FROM aio_requisicion WHERE id=:id LIMIT 1");
				$b->bindParam(':id',$id_requisicion);
				$b->execute();
				if ($b->fetchColumn() == 0 ) throw new Exception("No existe la requisicion");

				//Desidir si se activa o no
			 	if ($hacer=='activar_pdf')  $pdf = 'si';
			 	else $pdf = 'no';

			 	//Actualizar campo pdf
			 	$u = $con->prepare('UPDATE aio_requisicion SET pdf=:pdf , autorizo=:autorizo WHERE id=:id LIMIT 1');
			 	$u->bindParam(':pdf',$pdf);
			 	$u->bindParam(':autorizo', $user_logueado->id_usuario);
			 	$u->bindParam(':id',$id_requisicion);
			 	$u->execute();

				//Realizamos la escritura
				$con->commit();

			 	if ( $hacer == 'activar_pdf' ){
			 		//Buscar toda la informacion de la requisicion
			 		$b = $con->prepare("SELECT * FROM aio_requisicion WHERE id=:id LIMIT 1");
			 		$b->bindParam(':id',$id_requisicion);
			 		$b->execute();
			 		$requi = $b->fetchObject();

					// get the HTML
					ob_start();
					include('requisicionpdf.php');
					$html = ob_get_clean();
					
					require_once(__DIR__ . "/../../functions/html2pdf/html2pdf.class.php");
					$archivo = 'Requisicion-'.str_pad($id_requisicion, 4, '0', STR_PAD_LEFT).'.pdf';
					$html2pdf = new HTML2PDF('P','LETTER','es',array('mL', 'mT', 'mR', 'mB'));
					$html2pdf->pdf->SetDisplayMode('fullpage');
					$html2pdf->pdf->SetAuthor('Surtidores Martha');
					$html2pdf->WriteHTML($html);
					$html2pdf->setDefaultFont('helvetica');	
					$html2pdf->Output($archivo,'F');

				 	//Mensaje de exito
					$data = array(
						'r' 	   	 => 1,
						'mensaje' 	 => 'Ya se encuentra disponible el PDF',
						'id_requisicion' 	 => $id_requisicion,
						'hacer' 		 => $hacer,
						'archivo'	 => $archivo
					);
			 	}else{
				 	//Mensaje de exito
					$data = array(
						'r' 	   	 => 1,
						'mensaje' 	 => 'Se desactivo el PDF',
						'id_requisicion' 	 => $id_requisicion,
						'hacer' 		 => $hacer
					);
			 	}
			}catch( Exception $e){
				try { $con->rollBack(); } catch (Exception $e2) {}
				$data = array(
					'r' 	  	=> 0,
					'mensaje' 	=> $e->getMessage(),
					'id_requisicion' 	=> $id_requisicion,
					'hacer'		=> 'nada'
				);
			}
		break;

		case 'imprimir_pdf':
			try {
				//Desencriptar la requisicion
				$id_requisicion = desencriptar($id_requisicion);

			 	//Buscar existencia de la requisicion
				$b = $con->prepare("SELECT COUNT(id) FROM aio_requisicion WHERE id=:id LIMIT 1");
				$b->bindParam(':id',$id_requisicion);
				$b->execute();
				if ($b->fetchColumn() == 0 ) throw new Exception("No existe la requisicion");

		 		//Buscar toda la informacion de la requisicion
		 		$b = $con->prepare("SELECT * FROM aio_requisicion WHERE id=:id LIMIT 1");
		 		$b->bindParam(':id',$id_requisicion);
		 		$b->execute();
		 		$requi = $b->fetchObject();

				// get the HTML
				ob_start();
				include('requisicionpdf.php');
				$html = ob_get_clean();
				
				require_once(__DIR__ . "/../../functions/html2pdf/html2pdf.class.php");
				$archivo = 'Requisicion-'.str_pad($id_requisicion, 4, '0', STR_PAD_LEFT).'.pdf';
				$html2pdf = new HTML2PDF('P','LETTER','es',array('mL', 'mT', 'mR', 'mB'));
				$html2pdf->pdf->SetDisplayMode('fullpage');
				$html2pdf->pdf->SetAuthor('Surtidores Martha');
				$html2pdf->WriteHTML($html);
				$html2pdf->setDefaultFont('helvetica');	
				$html2pdf->Output($archivo,'F');

			 	//Mensaje de exito
				$data = array(
					'r' 	   	 => 1,
					'mensaje' 	 => 'Ya se encuentra disponible el PDF',
					'id_requisicion' 	 => $id_requisicion,
					'hacer' 		 => $hacer,
					'archivo'	 => $archivo
				);
			}catch( Exception $e){
				$data = array(
					'r' 	  	=> 0,
					'mensaje' 	=> $e->getMessage(),
					'id_requisicion' 	=> $id_requisicion,
					'hacer'		=> 'nada'
				);
			}
		break;

		case 'borrar':
			try {
				$id_requisicion = desencriptar($id_requisicion);
				$d = $con->prepare("DELETE FROM aio_requisicion WHERE id=:id");
				$d->bindParam(':id',$id_requisicion);
				$d->execute();

				$d = $con->prepare("DELETE FROM aio_requisicion_material WHERE id_requisicion=:id_requisicion");
				$d->bindParam(':id_requisicion',$id_cotizacion);
				$d->execute();

				$data = array(
					'r' 	  => 1,
					'mensaje' => 'Se elimino la requisicion'
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
