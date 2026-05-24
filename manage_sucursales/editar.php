<?php 	
	session_start();
	include('../funciones.php');
	registrado();

	//Conexion de BD
	$con = conecta();


	//Limpiar y desencriptar la bariable que llega desde get
	$id_sucursal = limpiar( desencriptar($_GET['id']) );

	$c = $con->prepare("SELECT COUNT(id) FROM aio_sucursal WHERE id_sucursal=:id");
	$c->bindParam(':id',$id_sucursal);
	$c->execute();

	if ( $c->fetchColumn() == 0 ) {
		header("location:table.php");
	}else{
		$b = $con->prepare("SELECT * FROM aio_sucursal WHERE id_sucursal=:id");
		$b->bindParam(':id',$id_sucursal);
		$b->execute();
		$r = $b->fetchObject();
	}

?>

<!DOCTYPE HTML>
<html lang="es">
<head>
	<?php include('../modules/header.php'); ?>	
	<title>Cliente <?php echo $id_sucursal; ?></title>
	<script src="<?php echo _BASE_URL; ?>/assets/js/chained.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){

			$("#municipio_envio").chained("#estado_envio");
			$("#municipio").chained("#estado");
			$("#estado_envio").on('change', function(event) {
 				$.uniform.update();
		    });
			$("#estado").on('change', function(event) {
 				$.uniform.update();
		    });

		});
	</script>
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
			<h2 class="icon frames">Sucursal <i><?php echo $r->nombre; ?></i></h2>			
		</div>

		<div class="box_content padding">
			<form action="enviar.php" name="agregar_usuario" id="agregar_usuario" class="validar" method="post" enctype="multipart/form-data">				
				<div class="field">
					<label class="left">Nombre de la Sucursal*</label>
					<input type="text" name="nombre" id="nombre" class="validate[required] big" value="<?php echo $r->nombre; ?>" placeholder="Nombre de la sucursal" />
				</div>					

				<div class="field">
					<label class="left">Telefono</label>
					<input type="text" name="tel" id="tel" class="big validate[custom[phone]]" placeholder="XXX-XX-XX" value="<?php echo $r->telefono; ?>" />
				</div>

				<div class="field">
					<label class="left">Celular</label>
					<input type="text" name="cel" id="cel" class="big validate[custom[phone]]" placeholder="044-614-XXX-XX-XX" value="<?php echo $r->celular; ?>" />
				</div>

				<div class="field">
					<label class="left">Razon Social*</label>
					<input type="text" name="razonsocial" id="razonsocial" class="big validate[required]" value="<?php echo $r->razon_social; ?>" />
				</div>

				<div class="field">
					<label class="left">RFC*</label>
					<input type="text" name="rfc" id="rfc" class="big validate[required]" value="<?php echo $r->rfc; ?>" />
				</div>				

				<div class="field">
					<label class="left">Calle*</label>
					<input type="text" name="calle" id="calle" class="validate[required]" placeholder="Calle" value="<?php echo $r->calle;?>" />
				</div>
				<div class="field">
					<label class="left">Numero*</label>
					<input type="text" name="noext" id="noext" class="peque validate[required]" placeholder="#" value="<?php echo $r->noext; ?>" />
					<input type="text" name="int" id="int" class="peque" placeholder="Int" value="<?php echo $r->interior; ?>" />
				</div>
				<div class="field">
					<label class="left">Colonia*</label>
					<input type="text" name="colonia" id="colonia" class="validate[required]" placeholder="Colonia" value="<?php echo $r->colonia; ?>" />
				</div>
				<div class="field">
					<label class="left">Codigo Postal</label>
					<input type="text" name="cp" id="cp" class="small" placeholder="#" value="<?php echo $r->cp; ?>" />
				</div>

				<div class="field">
					<label class="left">Estado y municipio*</label>
					<select name="estado" id="estado" class="validate[required]">
						<option value="">Seleccione</option>
						<?php
							$b = $con->prepare("SELECT id,nombre FROM aio_estados");
							$b->execute();
							$b->bindColumn('id',$clave);
							$b->bindColumn('nombre',$estado);						
							while ( $resultado = $b->fetch() ){

								if ( $r->estado == $clave ) $t = 'selected="selected"';
								else $t = '';
								echo '<option value="'.$clave.'" '.$t.'>'.$estado.'</option>';
							}
						?>
					</select>

					<select name="municipio" id="municipio" class="validate[required]">
						<option value="">Seleccione</option>
						<?php
							//Buscar y seleccionar el municipio guardado
							$b = $con->prepare("SELECT clave,nombre,estado_id FROM aio_municipios");
							$b->execute();

							while ( $resultado = $b->fetchObject() ){
								if ( $resultado->clave == $r->municipio && $resultado->estado_id == $r->estado ) $selected = 'selected=true';
								else $selected = '';

								echo '<option value="'.$resultado->clave.'" class="'.$resultado->estado_id.'" '.$selected.'>'.$resultado->nombre.'</option>';
							}
						?>
					</select>
				</div>

				<div class="field">
					<input type="text" name="empresa" id="empresa" value="<?php echo $r->nombre; ?>" readonly="true" class="small validate[required] text-input" style="display:none;" />
					<input type="text" name="id_sucursal" id="id_sucursal" value="<?php echo $id_sucursal; ?>" readonly="true" class="small validate[required] text-input" style="display:none;" />
					<input type="text" name="hacer" id="hacer" value="editar" readonly="true" class="small validate[required] text-input" style="display:none;" />
					<button>Guardar</button>
				</div>
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
