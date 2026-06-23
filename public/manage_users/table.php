<?php 	
	session_start();
	include(__DIR__ . "/../../funciones.php");
	
	//Comprobar Inicio de Sesion
	registrado();

	//Conexion de BD
	$con = conecta();

	//Obtener los trabajos que ahi en la BD para las categorias
	$b = $con->prepare("SELECT trabajos FROM aio_trabajos");
	$b->execute();
	$var = '';
	while ( $r = $b->fetchObject() ) $var.= '"'.$r->trabajos.'", ';

	//variable para evitar
	$evitar = 0;
?>

<!DOCTYPE HTML>
<html lang="es">
<head>

	<?php include(__DIR__ . "/../../modules/header.php"); ?>	
	<title>Administrar Usuarios</title>
	<link rel="stylesheet" href="<?php echo _BASE_URL; ?>/assets/js/select2/select2.css">
	<script src="<?php echo _BASE_URL; ?>/assets/js/select2/select2.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){

			$("#categoria").select2({tags:[<?php echo trim(trim($var),',') ?>]});

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
	<?php include(__DIR__ . "/../../modules/menu.php"); ?>
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
				$c_usuarios = $con->prepare("SELECT COUNT(id) FROM aio_personal WHERE id!='1'");
				$c_usuarios->execute();
				$numero = $c_usuarios->fetchColumn();
			?>
			<h2 class="icon pages">Usuarios en el sistema<span class="tip" title="Num. usuarios registrados"><?php echo $numero; ?></span></h2>
			
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
								<th class="align_left center">Nombre</th>
								<th class="align_left center">eMail</th>
								<th class="align_left center">Nivel</th>
								<th class="align_left center tools">Tools</th>
							</tr>
						</thead>
						<tbody>
						
							<?php 
								$b_usuarios = $con->prepare("SELECT * FROM aio_personal WHERE id!='1'");
								$b_usuarios->execute();
								while ( $r = $b_usuarios->fetchObject() ):
							?>
							<tr>
								<td class="align_left"><?php echo $r->id_usuario; ?></td>
								<td class="align_left center"><?php echo $r->nombre."&nbsp;".$r->apellido; ?></td>
								<td class="align_left center">
									<?php 
										if ( $r->nivel == 0 ) {
											echo "Sin email";
										}else echo $r->email; 
									?>
								</td>
								<td class="align_left center">
									<?php
										switch ( $r->nivel ) {
											case 0:
												echo "Sin acceseso";
											break;

											case 1:
												echo "Administrador";
											break;
											
											case 2:
												echo "Jefe de taller";
											break;
										}
									?>
								</td>
								<td class="align_left tools center">
									<a href="editar.php?id=<?php echo encriptar($r->id_usuario); ?>"  target="_blank" class="view tip" title="Editar">Editar</a>
									<?php if ( $r->nivel != 0 ): ?>
										<a href="editar_password.php?id=<?php echo encriptar($r->id_usuario); ?>" target="_blank" class="edits tip" title="Cambiar contraseña">Password</a>
									<?php endif; ?>
									<?php if ($evitar != 1): ?>
										<a href="#borrar_<?php echo $r->id_usuario; ?>" class="popup delete tip" title="borrar">borrar</a>
									<?php endif; ?>
								</td>
							</tr>
							
							<?php
								//Ventanda de nyro modal para borrar imagen
								$delete_ventana[] = "
									<div name='borrar_".$r->id_usuario."' id='borrar_".$r->id_usuario."'  style='display:none;'>
										<form action='enviar.php' name='borrar' id='borrar' class='validar' method='post'>
											<h1>¿Seguro que quiere <b>Borrar</b> a <b><i>".$r->nombre."&nbsp;".$r->apellido."</i></b>?</h1>
											<button>Eliminar</button>
											<button class='nyroModalClose'>Cerrar</button>
											<input type='hidden' name='id' id='id_borrar' value='".$r->id_usuario."' readonly='true' class='validate[required]' style='display:none;' />
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
				<?php 
					if ( !empty($delete_ventana) ){ foreach ($delete_ventana as $ventana) echo $ventana;}
					if ( !empty($editar_ventana) ){ foreach ($editar_ventana as $ventana) echo $ventana;}
				?>
				
				<!-- Agregar usuario: Start -->
				<div id="addnews" class="padding">
					<form action="enviar.php" name="agregar_usuario" id="agregar_usuario" class="validar" method="post" enctype="multipart/form-data">
						<p class="note">
							<span class="icon info"></span>
							Debe rellenar todos los campos
						</p>

						<div class="field">
							<label>Nombre completo*</label>
							<div class="row">
								<div class="col-lg-6"><input type="text" name="nombre" id="nombre" class="small form-control validate[required]" title="Nombres" placeholder="Nombres" /></div>
								<div class="col-lg-6"><input type="text" name="apellido" id="apellido" class="form-control validate[required]" title="Apellidos" placeholder="Apellidos" /></div>
							</div>
						</div>				    	
				    	
		           				<div class="field">
					 		<label class="left">Telefono</label>
					 		<input type="text" name="telefono" id="telefono_agregar" autocomplete="off" class="form-control medium" placeholder="4XX-XX-XX" />
				   		</div>

		           				<div class="field">
					 		<label class="left">Categorias</label>
					 		<input type="hidden" name="categoria" id="categoria" class="validate[required]" style="width:300px" />
				   		</div>

				   		<div class="field">
				   			<label class="left">Salario por hora</label>
				   			<input type="text" name="salario" id="salario" class="form-control validate[required,custom[number]]" placeholder="100.00" />
				   		</div>

				   		<div class="field">
				   			<label class="left">Sucursal</label>
				   			<select name="sucursal" id="sucursal" class="form-control validate[required]">
				   				<option value="">Seleccionar</option>
				   				<?php 
				   					$b = $con->prepare("SELECT id_sucursal,nombre FROM aio_sucursal");
				   					$b->execute();
				   					while ( $r = $b->fetchObject() ):
				   				?>
				   					<option value="<?php echo $r->id_sucursal; ?>"><?php echo $r->nombre; ?></option>
				   				<?php endwhile; ?>
				   			</select>

				   		</div>

						<div class="field">
					 		<label class="left">¿Acceso Dashboard?</label>
					 		<select name="acceseso" id="acceseso" class="form-control validate[required]">
					 			<option value="">Seleccione</option>
					 			<option value="0">No</option>
					 			<option value="1">Si</option>
					 		</select>
				   		</div>

				   		<div id="form_acceseso" style="display:none;">

				   			<div class="field">
				   				<label class="left">Nivel</label>
				   				<select name="nivel" id="nivel" class="form-control validate[required]">
				   					<option value="">Seleccione</option>
				   					<option value="1">Administrador</option>
				   					<option value="2">Jefe de taller</option>
				   				</select>
				   			</div>
				   		
			           				<div class="field">
						 		<label class="left">eMail*</label>
						 		<input type="text" name="email" id="email_agregar" autocomplete="off" class="medium form-control validate[required,custom[email]] tip-stay" title="Ingrese correo" />
					   		</div>
					   				        
			           				<div class="field">
						 		<label class="left">Password*</label>
						 		<input type="password" name="password" id="password" autocomplete="off" class="medium form-control validate[required] tip-stay" value="" title="Ingrese Password" />
							</div>
			        
							<div class="field">
								<label class="left">Confirmar Password*</label>
								<input type="password" name="password2" id="password2" autocomplete="off" class="medium form-control validate[required,equals[password]] tip-stay" value="" title="Confirme password" />
							</div>
						</div>

		        				<input type="text" name="hacer" id="hacer" value="insertar" readonly="true" class="small form-control validate[required] text-input" style="display:none;" />
						<button class="btn btn-lg btn-success">Guardar</button>										
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
