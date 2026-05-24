<?php 	
	session_start();
	include('../funciones.php');
	registrado();

	//Conexion de BD
	$con = conecta();


	//Limpiar y desencriptar la bariable que llega desde get
	$id_cliente = limpiar( desencriptar($_GET['id']) );

	$c = $con->prepare("SELECT COUNT(id_usuario) FROM aio_personal WHERE id_usuario=:id");
	$c->bindParam(':id',$id_cliente);
	$c->execute();

	if ( $c->fetchColumn() == 0 ) {
		header("location:table.php");
	}else{
		$b = $con->prepare("SELECT * FROM aio_personal WHERE id_usuario=:id");
		$b->bindParam(':id',$id_cliente);
		$b->execute();
		$r = $b->fetchObject();
	}

	//Obtener los trabajos que ahi en la BD para las categorias
	$trabajos = $con->prepare("SELECT trabajos FROM aio_trabajos");
	$trabajos->execute();
	$var = '';
	while ( $w = $trabajos->fetchObject() ) $var.= '"'.$w->trabajos.'", ';	
?>

<!DOCTYPE HTML>
<html lang="es">
<head>
	<?php include('../modules/header.php'); ?>	
	<title>Personal <?php echo $r->nombre.' '.$r->apellido; ?></title>

	<link rel="stylesheet" href="<?php echo _BASE_URL; ?>/assets/js/select2/select2.css">
	<script src="<?php echo _BASE_URL; ?>/assets/js/select2/select2.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){

			$("#categoria").select2({
				tags:[<?php echo trim(trim($var),',') ?>]
			});

			$("#acceseso").change(function(){
				var acceseso = $(this).val();
				if ( acceseso == '0' ){
					$("#form_acceseso").hide('slow');
				}else if ( acceseso == '1' ){
					$("#form_acceseso").show('slow');
				}else{
					$("#form_acceseso").hide('slow');
				}
			});

		});
	</script>
	
</head>
<body>

<!-- Start: Page Wrap -->
<div id="wrap" class="container_24">

	<!-- Menu: Start -->	
	<?php include('../modules/menu.php'); ?>
	<!-- Menu: End -->

	<!-- Form aticulos-->
	<div class="grid_24">
		<div class="box_top">
			<h2 class="icon frames">Editar informacion de <i><?php echo $r->nombre.' '.$r->apellido; ?></i></h2>			
		</div>

		<div class="box_content padding">
			<form action="enviar.php" name="editar_personal" id="editar_personal" class="validar" method="post" enctype="multipart/form-data">

				<div class="field">
					<label class="left">Nombre completo*</label>
					<input type="text" name="nombre" id="nombre" value="<?php echo $r->nombre; ?>" class="small validate[required]" title="Nombres" placeholder="Nombres" />
					<input type="text" name="apellido" id="apellido" value="<?php echo $r->apellido; ?>" class="validate[required]" title="Apellidos" placeholder="Apellidos" />
				</div>				    	
		    	
	       		<div class="field">
			 		<label class="left">Telefono</label>
			 		<input type="text" name="telefono" id="telefono_agregar" value="<?php echo $r->telefono; ?>" autocomplete="off" class="medium" placeholder="4XX-XX-XX" />
		   		</div>

	       		<div class="field">
			 		<label class="left">Categorias</label>
			 		<input type="hidden" name="categoria" id="categoria" value="<?php echo $r->categoria; ?>" class="validate[required]" style="width:300px" />
		   		</div>

		   		<div class="field">
		   			<label class="left">Salario por hora</label>
		   			<input type="text" name="salario" id="salario" value="<?php echo $r->salario; ?>" class="validate[required,custom[number]]" placeholder="100.00" />
		   		</div>

		   		<div class="field">
		   			<label class="left">Sucursal</label>
		   			<select name="sucursal" id="sucursal" class="validate[required]">
		   				<option value="">Seleccionar</option>
		   				<?php 
		   					$b = $con->prepare("SELECT id_sucursal,nombre FROM aio_sucursal");
		   					$b->execute();
		   					while ( $sucursal = $b->fetchObject() ):
		   						if ( $r->sucursal == $sucursal->id_sucursal ) $s = 'selected="true"';
		   						else $s = '';
		   				?>
		   					<option value="<?php echo $sucursal->id_sucursal; ?>" <?php echo $s; ?> ><?php echo $sucursal->nombre; ?></option>
		   				<?php endwhile; ?>
		   			</select>

		   		</div>

				<div class="field">
			 		<label class="left">¿Acceso Dashboard?</label>
			 		<select name="acceseso" id="acceseso" class="validate[required]">
			 			<option value="">Seleccione</option>
			 			<option value="0" <?php if ( $r->nivel == 0 ) echo 'selected="true"'; ?> >No</option>
			 			<option value="1" <?php if ( $r->nivel != 0 ) echo 'selected="true"'; ?> >Si</option>
			 		</select>
		   		</div>

		   		<div id="form_acceseso" <?php if ( $r->nivel == 0 ) echo 'style="display:none;"';  ?> >

		   			<div class="field">
		   				<label class="left">Nivel</label>
		   				<select name="nivel" id="nivel" class="validate[required]">
		   					<option value="">Seleccione</option>
		   					<option value="1" <?php if( $r->nivel == 1 ) echo 'selected="true"'; ?> >Administrador</option>
		   					<option value="2" <?php if( $r->nivel == 2 ) echo 'selected="true"'; ?> >Jefe de taller</option>
		   				</select>
		   			</div>
		   		
	           		<div class="field">
				 		<label class="left">eMail*</label>
				 		<input type="text" name="email" id="email_agregar" value="<?php echo $r->email; ?>" autocomplete="off" class="medium validate[required,custom[email]] tip-stay" title="Ingrese correo" />
			   		</div>
			   				        
	           		<div class="field">
				 		<label class="left">Password*</label>
				 		<input type="password" name="password" id="password" autocomplete="off" class="medium validate[equals[password2]] tip-stay" value="" title="Ingrese Password" />
					</div>
	        
					<div class="field">
						<label class="left">Confirmar Password*</label>
						<input type="password" name="password2" id="password2" autocomplete="off" class="medium validate[equals[password]] tip-stay" value="" title="Confirme password" />
					</div>
				</div>

				<input type="text" name="id" id="id" value="<?php echo $id_cliente; ?>" readonly="true" class="small validate[required] text-input" style="display:none;" />
	    		<input type="text" name="hacer" id="hacer" value="editar" readonly="true" class="small validate[required] text-input" style="display:none;" />
				<button>Guardar</button>
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