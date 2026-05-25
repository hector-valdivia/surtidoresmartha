<?php 	
	session_start();
	include(__DIR__ . "/../../funciones.php");
	
	//Comprobar Inicio de Sesion
	registrado();

	//Conexion de BD
	$con = conecta();
?>

<!DOCTYPE HTML>
<html lang="es">
<head>
	<?php include(__DIR__ . "/../../modules/header.php"); ?>	
	<title>Sucursales</title>
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
</head>

<body>

<!-- Start: Page Wrap -->
<div id="wrap" class="container_24">
		
	<!-- Menu: Start -->	
	<?php include(__DIR__ . "/../../modules/menu.php"); ?>
	<!-- Menu: End -->
		
	<?php mostrar_errores(); ?>

	<!-- 100% Box Grid Container: Start -->
	<div class="grid_24">

		<!-- Box Header: Start -->
		<div class="box_top">
			<?php
				$c_usuarios = $con->prepare("SELECT COUNT(id) FROM aio_sucursal");
				$c_usuarios->execute();
				$numero = $c_usuarios->fetchColumn();
			?>
			<h2 class="icon pages">Sucursales<span class="tip" title="Num. Sucursales"><?php echo $numero; ?></span></h2>
			
			<!-- Tab Select: Start -->
			<ul class="sorting">
				<li><a href="#listing" class="active">Lista</a></li>
				<li><a href="#addnews">Agregar Sucursal</a></li>
			</ul>
			<!-- Tab Select: End -->
		</div>
		<!-- Box Header: End -->
		
		<!-- Box Content: Start -->
		<div class="box_content">
			
			<!-- News Table Tabs: Start -->
			<div class="tabs">
			
				<!-- News Sorting Table: Start -->
				<div id="listing">
					
					<table class="sorting">
						<thead>
							<tr>
								<th class="align_left">ID Sucursal</th>
								<th class="align_left center">Sucursal</th>								
								<th class="align_left center">Direccion</th>
								<th class="align_left center tools">Tools</th>
							</tr>
						</thead>
						<tbody>
						
							<?php								
								$b_usuarios = $con->prepare("SELECT * FROM aio_sucursal");
								$b_usuarios->execute();	
								while ( $r = $b_usuarios->fetchObject() ):
							?>
							<tr>
								<td class="align_left"><?php echo $r->id_sucursal; ?></td>
								<td class="align_left"><?php echo $r->nombre; ?></td>
								<td class="align_left center"><?php echo nombre_estado($r->estado).','.nombre_municipio($r->municipio,$r->estado).' '.$r->calle.' '.$r->colonia; ?></td>
								<td class="align_left tools center">
									<a href="editar.php?id=<?php echo encriptar($r->id_sucursal); ?>"  target="_blank" class="view tip" title="Editar">Editar</a>
									<a href="#borrar_<?php echo $r->id_sucursal; ?>" class="popup delete tip" title="Borrar">Borrar</a>									
								</td>
							</tr>
							
							<?php
								//Ventanda de nyro modal para borrar imagen
								$delete_ventana[] = "
									<div name='borrar_".$r->id_sucursal."' id='borrar_".$r->id_sucursal."'  style='display:none;'>
										<form action='enviar.php' name='borrar_gal' id='borrar_gal' class='validar' method='post'>
											<h1>¿Seguro que quiere <b>Borrar</b> a la sucursal <b><i>".$r->nombre."</i></b>?</h1>
											<button>Eliminar</button>
											<button class='nyroModalClose'>Cerrar</button>
											<input type='hidden' name='id' id='id' value='".$r->id_sucursal."' readonly='true' class='validate[required]' style='display:none;' />
											<input type='hidden' name='hacer' id='hacer_borrar' value='borrar' readonly='true' class='validate[required]' style='display:none;' />
										</form>																	
									</div>";					 
							
								endwhile; 
							?>
													
						</tbody>
					</table> 
	
				</div>
				<!-- News Sorting Table: End -->
				<?php 
					if ( !empty($delete_ventana) ){ foreach ($delete_ventana as $ventana) echo $ventana;}
				?>
				
				<!-- Agregar Sucursal: Start -->
				<div id="addnews" class="padding">
					<form action="enviar.php" name="agregar_usuario" id="agregar_usuario" class="validar" method="post" enctype="multipart/form-data">
					
						<div class="field">
							<label class="left">Nombre de la Sucursal*</label>
							<input type="text" name="nombre" id="nombre" class="validate[required] big" placeholder="Nombre de la sucursal" />
						</div>						

						<div class="field">
							<label class="left">Telefono</label>
							<input type="text" name="tel" id="tel" class="big validate[custom[phone]]" placeholder="XXX-XX-XX" />					
						</div>

						<div class="field">
							<label class="left">Celular</label>
							<input type="text" name="cel" id="cel" class="big validate[custom[phone]]" placeholder="044-614-XXX-XX-XX" />					
						</div>

						<div class="field">
							<label class="left">Razon Social*</label>
							<input type="text" name="razonsocial" id="razonsocial" class="big validate[required]" />
						</div>

						<div class="field">
							<label class="left">RFC*</label>
							<input type="text" name="rfc" id="rfc" class="big validate[required]" />
						</div>				

						<div class="field">
							<label class="left">Calle*</label>
							<input type="text" name="calle" id="calle" class="validate[required]" placeholder="Calle" />
						</div>

						<div class="field">
							<label class="left">Numero*</label>
							<input type="text" name="noext" id="noext" class="peque validate[required]" placeholder="#" />
							<input type="text" name="int" id="int" class="peque" placeholder="Int" />
						</div>

						<div class="field">
							<label class="left">Colonia*</label>
							<input type="text" name="colonia" id="colonia" class="validate[required]" placeholder="Colonia" />
						</div>

						<div class="field">
							<label class="left">Codigo Postal</label>
							<input type="text" name="cp" id="cp" class="small" placeholder="#" />					
						</div>				

						<div class="field">
							<label class="left">Estado y municipio*</label>
							<select name="estado" id="estado" class="validate[required]">
								<option value="" selected="true">Seleccione</option>
								<option value="8">Chihuahua</option>
								<?php
									$b = $con->prepare("SELECT id,nombre FROM aio_estados WHERE nombre!='Chihuahua'");
									$b->execute();
									$b->bindColumn('id',$clave);
									$b->bindColumn('nombre',$estado);						
									while ( $r = $b->fetch() ){
										echo '<option value="'.$clave.'">'.$estado.'</option>';
									}
								?>
							</select>
							<select name="municipio" id="municipio" class="validate[required]">
								<option value="" selected="true">Seleccione</option>
								<?php
									$b = $con->prepare("SELECT clave,nombre,estado_id FROM aio_municipios");
									$b->execute();
									$b->bindColumn('clave',$clave);
									$b->bindColumn('nombre',$municipio);
									$b->bindColumn('estado_id',$estado_id);

									while ( $r = $b->fetch() ){
										echo '<option value="'.$clave.'" class="'.$estado_id.'">'.$municipio.'</option>';
									}
								?>
							</select>
						</div>

						<div class="field">
	    					<input type="text" name="hacer" id="hacer" value="insertar" readonly="true" class="small validate[required] text-input" style="display:none;" />
	    					<button>Guardar</button>
	    				</div>
					</form>
				</div>
				<!-- Agregar usuario: end -->			
			</div>
			<!-- News Table Tabs: End -->	
		</div>
		<!-- Box Content: End -->

	</div>
	<!-- 100% Box Grid Container: End -->

	<!-- Footer Grid: Start -->
	<?php include(__DIR__ . "/../../modules/pie.php"); ?>
	<!-- Footer Grid: End -->

</div>
<!-- End: Page Wrap -->

<!-- funciones de jquery: start -->
<?php include(__DIR__ . "/../../modules/js.php"); ?> 
<!-- funciones de jquery: end -->

</body>

</html>