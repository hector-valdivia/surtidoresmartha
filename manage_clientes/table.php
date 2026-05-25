<?php 	
	session_start();
	include('../funciones.php');
	
	//Comprobar Inicio de Sesion
	registrado();

	//Conexion de BD
	$con = conecta();

	//variable para evitar
	$evitar = 0;
?>

<!DOCTYPE HTML>
<html lang="es">
<head>

	<?php include('../modules/header.php'); ?>	
	<title>Administrar Clientes</title>
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
	<?php include('../modules/menu.php'); ?>
	<!-- Menu: End -->
		
	<?php mostrar_errores(); ?>

	<!-- 100% Box Grid Container: Start -->
	<div class="grid_24">

		<!-- Box Header: Start -->
		<div class="box_top">
			<?php
				$c_usuarios = $con->prepare("SELECT COUNT(id) FROM aio_cliente WHERE id!='1'");
				$c_usuarios->execute();
				$numero = $c_usuarios->fetchColumn();
			?>
			<h2 class="icon pages">Clientes<span class="tip" title="Num. usuarios registrados"><?php echo $numero; ?></span></h2>
			
			<!-- Tab Select: Start -->
			<ul class="sorting">
				<li><a href="#listing" class="active">Lista</a></li>
				<li><a href="#addnews">Agregar Usuario</a></li>
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
								<th class="align_left">ID usuario</th>
								<th class="align_left center">Cliente</th>								
								<th class="align_left center">eMail</th>
								<th class="align_left center tools">Tools</th>
							</tr>
						</thead>
						<tbody>
						
							<?php								
								$b_usuarios = $con->prepare("SELECT * FROM aio_cliente");
								$b_usuarios->execute();	
								while ( $r = $b_usuarios->fetchObject() ):
							?>
							<tr>
								<td class="align_left"><?php echo $r->id_cliente; ?></td>
								<td class="align_left center"><?php echo $r->cliente; ?></td>
								<td class="align_left center"><?php echo $r->email; ?></td>
								<td class="align_left tools center">
									<a href="editar.php?id=<?php echo encriptar($r->id_cliente); ?>"  target="_blank" class="view tip" title="Editar">Editar</a>
									<?php if ($evitar != 1): ?>
										<a href="#borrar_<?php echo $r->id_cliente; ?>" class="popup delete tip" title="Borrar">Borrar</a>
									<?php endif; ?>
								</td>
							</tr>
							
							<?php
								//Ventanda de nyro modal para borrar imagen
								$delete_ventana[] = "
									<div name='borrar_".$r->id_cliente."' id='borrar_".$r->id_cliente."'  style='display:none;'>																								
										<form action='enviar.php' name='borrar_gal' id='borrar_gal' class='validar' method='post'>																																																									
											<h1>¿Seguro que quiere <b>Borrar</b> al cliente <b><i>".$r->cliente."</i></b>?</h1>
											<button>Eliminar</button>
											<button class='nyroModalClose'>Cerrar</button>																													
											<input type='hidden' name='id' id='id' value='".$r->id_cliente."' readonly='true' class='validate[required]' style='display:none;' />
											<input type='hidden' name='hacer' id='hacer_borrar' value='borrar' readonly='true' class='validate[required]' style='display:none;' />
										</form>																	
									</div>";					 
							
								endwhile; 
							?>
													
						</tbody>
					</table>
					<div class="table_actions"><br></div>
				</div>
				<!-- News Sorting Table: End -->
				<?php  if ( !empty($delete_ventana) ){ foreach ($delete_ventana as $ventana) echo $ventana; } ?>
				
				<!-- Agregar usuario: Start -->
				<div id="addnews" class="padding">
					<form action="enviar.php" name="agregar_usuario" id="agregar_usuario" class="validar" method="post" enctype="multipart/form-data">

						<div class="row">
							<div class="col-lg-6">
								<div class="field">
									<label class="left">Empresa*</label>
									<input type="text" name="empresa" id="empresa" class="form-control validate[required]" placeholder="Empresa" />
								</div>

								<div class="field">
									<label class="left">Correo</label>
									<input type="text" name="email" id="email" class="form-control validate[required,custom[email]]" placeholder="correo@dominio.com" />
								</div>							

								<div class="field">
									<label class="left">Telefono</label>
									<input type="text" name="tel" id="tel" class="big form-control validate[groupRequired[tel],custom[phone]]" placeholder="XXX-XX-XX" />					
								</div>

								<div class="field">
									<label class="left">Celular</label>
									<input type="text" name="cel" id="cel" class="big form-control validate[groupRequired[tel],custom[phone]]" placeholder="044-614-XXX-XX-XX" />					
								</div>

								<div class="field">
									<label class="left">Razon Social*</label>
									<input type="text" name="razonsocial" id="razonsocial" class="big form-control validate[required]" />
								</div>
							</div>
							<div class="col-lg-6">
								<div class="field">
									<label class="left">RFC*</label>
									<input type="text" name="rfc" id="rfc" class="big form-control validate[required]" />
								</div>				

								<div class="field">
									<label class="left">Calle*</label>
									<input type="text" name="calle" id="calle" class="form-control validate[required]" placeholder="Calle" />
								</div>
								<div class="field">
									<label>Numero*</label>
									<div class="row">
										<div class="col-lg-6"><input type="text" name="noext" id="noext" class="form-control validate[required]" placeholder="#" /></div>
										<div class="col-lg-6"><input type="text" name="int" id="int" class="form-control" placeholder="Int" /></div>
									</div>
								</div>
								<div class="field">
									<div class="row">
										<div class="col-lg-6">
											<label class="left">Colonia*</label>
											<input type="text" name="colonia" id="colonia" class="form-control validate[required]" placeholder="Colonia" />
										</div>
										<div class="col-lg-6">
											<label class="left">Codigo Postal</label>
											<input type="text" name="cp" id="cp" class="form-control small" placeholder="#" />	
										</div>
									</div>
								</div>
								<div class="field">
									<label>Estado y municipio*</label>
									<div class="row">
										<div class="col-lg-6">
											<select name="estado" id="estado" class="form-control validate[required]">
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
										</div>
										<div class="col-lg-6">
											<select name="municipio" id="municipio" class="form-control validate[required]">
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
									</div>
								</div>
							</div>
						</div>

						<div class="field">
	    						<input type="text" name="hacer" id="hacer" value="insertar" readonly="true" class="small form-control validate[required] text-input" style="display:none;" />
	    						<button class="btn btn-success btn-lg btn-block">Guardar</button>
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
	<?php include('../modules/pie.php'); ?>
	<!-- Footer Grid: End -->

</div>
<!-- End: Page Wrap -->

<!-- funciones de jquery: start -->
<?php include('../modules/js.php'); ?> 
<!-- funciones de jquery: end -->

</body>

</html>