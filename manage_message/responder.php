<?php
	session_start();
	include('../../../funciones.php');
	
	//Comprobar Inicio de Sesion
	registrado();

	//Conexion de BD
	$con = conecta();
		
	if($_GET){
	    $keys_post = array_keys($_GET);
	    foreach ($keys_post as $key_post){
	     	$$key_post = $_GET[$key_post];
	     	error_log("variable $key_post viene desde $ _POST");
	    }
	}
		
	$b_mensaje = $con->prepare("SELECT nombre,mensaje,email FROM mensajes WHERE id=:id");
	$b_mensaje->bindParam(':id', $id);
	$b_mensaje->execute();
	$b_mensaje->bindColumn('nombre',$nombre );
	$b_mensaje->bindColumn('mensaje',$mensaje );
	$b_mensaje->bindColumn('email',$email );
	$b_mensaje->fetch();
	
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
	<div class="grid_24">	

		<div class="box_top">			
			<h2 class="icon pages">Responder el mensaje</h2>		
		</div>

		<!-- Box Content: Start -->
		<div class="box_content padding">
		
			<p class="note">Se responde a <i><?php echo $nombre; ?></i> al correo <i><?php echo $email; ?></i></p>
			<form action="table.php" name="responder" id="responder" method="post" class="validar" enctype="multipart/form-data">
							
				<div class="field">
					<label>Mensaje del usuario</label>
					<textarea name="mensaje_resivido" id="mensaje_resivido" readonly="readonly" rows="20" cols="50">
						<?php echo $mensaje; ?>
					</textarea>																						
				</div>           
				
				<div class="field">
					<label>Respuesta</label>
					<textarea name="mensaje" id="mensaje" class="validate[required] texto" rows="20" cols="50"></textarea>
				</div>
				
				<br><br>	
				<input type="text" name="email" id="email" value="<?php echo $email; ?>" readonly="true" class="small validate[required]" style="display:none;"/>
				<input type="text" name="id" id="id" value="<?php echo $id; ?>" readonly="true" class="small validate[required]" style="display:none;"/>		    
				<input type="text" name="hacer" id="hacer" value="responder" readonly="true" class="small validate[required]" style="display:none;"/>        
		
				<button>Responder</button>																						
			</form>
		</div>
	</div>											
	
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