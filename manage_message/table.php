<?php 

	session_start();
	include('../../../funciones.php');
	
	//Comprobar Inicio de Sesion
	registrado();

	//Conexion de BD
	$con = conecta();
		
	if($_POST){
	    $keys_post = array_keys($_POST);
	    foreach ($keys_post as $key_post){
	     	$$key_post = $_POST[$key_post];
	     	error_log("variable $key_post viene desde $ _POST");
	    }
	}
	
	switch($hacer) {
		
		case 'responder':
			//mail($to, $subject, $message);
			$id_usuario = limpiar($_SESSION['id']);
			
			$c_info = $con->prepare("SELECT COUNT(id) FROM personal WHERE id_usuario=:id");
			$c_info->bindParam(':id', $id_usuario);
			$c_info->execute();
			$numero = $c_info->fetchColumn();
			
			if ($numero != 0){
				
				//Buscar informacion del usuario que manda el mensaje						
				$b_info = $con->prepare("SELECT nombre,apellido,email FROM personal WHERE id_usuario=:id");
				$b_info->bindParam(':id', $id_usuario);
				$b_info->execute();
				$b_info->bindColumn('nombre', $nombre);
				$b_info->bindColumn('apellido', $apellido);
				$b_info->bindColumn('email', $email);
				$b_info->fetch();
				
				//Acomodando las variables a enviar
				$nombre_completo = $apellido.' '.$nombre;
				$dia_hora = date( 'Y-m-d H:i:s');
				$id = limpiar($id);
				
				$i_respuesta = $con->prepare("UPDATE mensajes SET respuesta=:respuesta, nombre_respuesta=:nombre_respuesta,respondido=:respondido WHERE id=:id ");
				$i_respuesta->bindParam(':respuesta', $mensaje);
				$i_respuesta->bindParam(':nombre_respuesta', $nombre_completo);
				$i_respuesta->bindParam(':respondido', $dia_hora);
				$i_respuesta->bindParam(':id', $id);
				$i_respuesta->execute();
				
				//Asunto
				$asunto = 'Respuesta Mera';
				
				//Contenido HTML
				$cabeceras = "MIME-Version: 1.0\r\n"; 
				$cabeceras.= "Content-type: text/html; charset=iso-8859-1\r\n"; 
		
				//direcci�n del remitente 
				$cabeceras.= "From: Mera <info@cmera.com>\r\n"; 
						
				mail($email,$asunto,$mensaje,$cabeceras);				
				
			}					
		break;			
		
		case 'borrar':	

			$d_mensaje = $con->prepare("DELETE FROM mensajes WHERE id=:id");
			$d_mensaje->bindParam(':id', $id);
			$d_mensaje->execute();
			$_SESSION['bien'][]='Mensaje borrado';
		
		break;
	}
?>

<!DOCTYPE HTML>
<html lang="es">
<head>

	<?php include('../modules/header.php'); ?>
	<title>Administrar Mensajes Web</title>

</head>

<body>

<!-- Start: Page Wrap -->
<div id="wrap" class="container_24">
	

	<!-- Menu: Start -->	
	<?php include('../modules/menu.php'); ?>
	<!-- Menu: End -->
		
	<?php 
		///Avisos de error o de exito generados con sesiones
		if(isset($_SESSION['error'])){
			echo '<div class="grid_24">'; 
			foreach ($_SESSION['error'] as $error)
				echo '<div class="notice warning"><p><b>Error: </b>'.$error.'</p></div>';
			unset($_SESSION['error']);
			echo '</div>';		 
		}
			
		if (isset($_SESSION['bien'])){
			echo '<div class="grid_24">';
			foreach ($_SESSION['bien'] as $bien)
				echo '<div class="notice success"><p><b>'.$bien.'</b></p></div>';
			unset($_SESSION['bien']);
			echo '</div>';		 
		}
	?>	


	<!-- 100% Box Grid Container: Start -->
	<div class="grid_24">

		<!-- Box Header: Start -->
		<div class="box_top">
			<?php
				$c_mensajes = $con->prepare("SELECT COUNT(id) FROM mensajes");
				$c_mensajes->execute();
				$numero = $c_mensajes->fetchColumn(); 
			?>
			
			<h2 class="icon pages">Mensajes enviados desde la web<span class="tip" title="Num. Mensajes"><?php echo $numero; ?></span></h2>			
		
		</div>
		<!-- Box Header: End -->
	
		<!-- Box Content: Start -->
		<div class="box_content">	
							
			<table class="sorting">
				<thead>
					<tr>
						<th class="align_left ">Nombre</th>
						<th class="align_left">eMail</th>
						<th class="align_left">Fecha</th>
						<th class="align_left">respondido</th>
						<th class="align_left center tools">Tools</th>
					</tr>
				</thead>
				<tbody>
					<?php
						$b_mensajes = $con->prepare("SELECT * FROM mensajes ORDER BY fecha");
						$b_mensajes->execute();
						$b_mensajes->bindColumn('id',$id );
						$b_mensajes->bindColumn('nombre',$nombre );
						$b_mensajes->bindColumn('mensaje',$mensaje );
						$b_mensajes->bindColumn('email',$email );
						$b_mensajes->bindColumn('fecha',$fecha );
						$b_mensajes->bindColumn('respondido',$respondido );
						$b_mensajes->bindColumn('respuesta',$respuesta );
						$b_mensajes->bindColumn('nombre_respuesta', $quien);

						
						while($r = $b_mensajes->fetch()):
					?>
					<tr>
						<td class="align_left "><?php echo $nombre; ?></td>
						<td class="align_left "><?php echo $email; ?></td>
						<td class="align_left "><?php echo $fecha; ?></td>
						<td class="align_left ">
							<?php 
								if (!empty($respondido)){
									echo '<a href="#respuesta_'.$id.'" class="popup">ver la respuesta</a>';
									$modal_respuestas[] = '
										<div name="respuesta_'.$id.'" id="respuesta_'.$id.'" style="display:none;">
											<div style="width:500px;"><h1>Respuesta dada por <b>'.$quien.'</b> a <b>'.$nombre.'</b> el dia y hora <b>'.$respondido.'</b></h1></div>
											<hr>											
											<div style="width:500px;">'.$respuesta.'</div>
										</div>'; 
								}else 
									echo '<a href="responder.php?id='.$id.'" class="edit tip" title="Responder">responder</a>';									
																 								 
							?>
						</td>
						<td class="align_left tools center">
							<a href="responder.php?id=<?php echo $id; ?>" class="edit tip" title="Responder">responder</a>
							<a href="#mensaje_<?php echo $id; ?>" class="popup view tip" title="Ver">ver</a>								
							<a href='#eliminar_<?php echo $id; ?>' class='popup delete tip' title='Borrar'>borrar</a>
						</td>
					</tr>						
					<?php

						$modal_mensaje[]='
							<div name="mensaje_'.$id.'" id="mensaje_'.$id.'" style="display:none;">
								<div style="width:500px;">
									<h1>Pregunta enviada por <b>'.$nombre.'</b></h1>
								</div>
								<hr>											
								<div style="width:500px;">'.$mensaje.'</div>
							</div>';
						
						$modal_eliminar[]='
							<div name="eliminar_'.$id.'" id="eliminar_'.$id.'" style="display:none;">										
								<h1>¿Seguro quiere eliminar la pregunta hecha por <b>'.$nombre.'</b>?</h1>																																							
								<form action="table.php" name="eliminar" id="eliminar" class="validar" method="post">
									<input type="text" name="id" id="id_eliminar" value="'.$id.'" readonly="readonly" style="display:none;" />
									<input type="text" name="hacer" id="hacer_eliminar" value="borrar" readonly="readonly" style="display:none;" />
									<center>			
										<button>Eliminar</button>
										<button class="nyroModalClose">Cerrar</button>
									</center>			
								</form>																					
							</div>';					
					
						endwhile; 
					?>					
				</tbody>
			</table>
			<?php 
				if (!empty($modal_respuestas)) foreach ($modal_respuestas as $modal) echo $modal;
				if (!empty($modal_mensaje)) foreach ($modal_mensaje as $modal) echo $modal;
				if (!empty($modal_eliminar)) foreach ($modal_eliminar as $modal) echo $modal;
			?>			 		
		</div>
		<!-- Box Content: End -->
	
	</div>
	<!-- 100% Box Grid Container: End -->

	
	<!-- Footer Grid: Start -->
	<?php include('../modules/pie.php'); ?>
	<!-- Footer Grid: End -->

</div>
<!-- End: Page Wrap -->

<!-- funciones de jquery: start -->
<?php include('../modules/js.php'); ?> 

<!-- funciones de jquery: end -->

</body>

</html>