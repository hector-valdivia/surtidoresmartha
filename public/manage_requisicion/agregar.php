<?php 	
	session_start();
	include(__DIR__ . "/../../funciones.php");
	registrado();

	//Conexión de BD
	$con = conecta();
	$user_logueado = info_personal( desencriptar( limpiar($_SESSION['id']) ) );

	if ( isset( $_GET['id'] ) ){
		$id = desencriptar($_GET['id']);

		$c = $con->prepare("SELECT COUNT(id) FROM aio_requisicion WHERE id=:id");
		$c->bindParam(':id',$id);
		$c->execute();
		if ( $c->fetchColumn() == 0 ) header('Location:table.php');
		else{
			$b = $con->prepare("SELECT * FROM aio_requisicion WHERE id=:id");
			$b->bindParam(':id',$id);
			$b->execute();

			$cot = $b->fetchObject();
		}
	}else{
        $id = '';
        $cot = new stdClass();
        $cot->bloqueada = null;
    }
?>

<!DOCTYPE HTML>
<html lang="es">
<head>
	<?php include(__DIR__ . "/../../modules/header.php"); ?>
	<title>Requisicion <?php echo $id; ?></title>
	<style>
		#pedido{ padding-bottom: 15px; padding-right: 20px; }
		#total { padding-right: 70px; }
		#total > p{ font-size: 22px; text-align: right; }
		.espacio > .titulo{
			background-color: #015;
			border-color: #292929;
			border-width: 1px;
			border-style: solid;
			height: 40px; 
			padding-top: 5px;
			margin-top: 10px;
			margin-left: 20px;
			font-weight: bold;
			color: #FFFFFF;
			-webkit-text-shadow: #000000 -1px -1px;
			-moz-text-shadow: #000000 -1px -1px;
			text-shadow: #000000 -1px -1px;
			-webkit-border-radius: 5px 5px 0 0;
			-moz-border-radius: 5px 5px 0 0;
			border-radius: 5px 5px 0 0;
			vertical-align: middle;
			text-align: center;
		}

		.aceptado{
			background-color: #419641 !important;
			border-color: #3e8f3e !important;
		}

		.cancelado{
			background-image: -webkit-linear-gradient(top, #f0ad4e 0%, #eb9316 100%) !important;
			background-image: linear-gradient(to bottom, #f0ad4e 0%, #eb9316 100%) !important;
			border-color: #e38d13 !important;
		}
		
		.espacio > .titulo > .material { 
			text-align: left;
			padding-left: 10px;
			text-overflow: ellipsis;
			white-space: nowrap;
			overflow: hidden;
			width: 55%;
			float: left;
			margin-top: 4px;
		}
		.espacio > .titulo > .botones {
			display: inline;
			float: right;
			margin: 0 10px;
		}

		.espacio > .titulo > .botones > a {
			color: white;
			font-size: 20px;
			margin-left: 5px;
		}

		.espacio > .titulo > .botones > div { display: inline; }

		.espacio > .titulo > .botones > div > a {
			color: white;
			font-size: 20px;
			margin-left: 5px;
		}

		.open>.dropdown-menu {
			display: block;
			list-style-type: none;
			margin: 0;
			padding: 0;
			color: black;
			text-align: left;
			text-shadow: none;
		}

		.dropdown-menu > li{
			margin: 0 !important;
			padding: 5px !important;
		}

		.dropdown-menu > li > a:hover, .dropdown-menu > li > a { cursor: pointer; }
		.dropdown-menu > li > a:hover, .dropdown-menu > li > a.selected {
			background: #4096ee; /* Old browsers */
			background: -moz-linear-gradient(top, #4096ee 0%, #4096ee 100%); /* FF3.6+ */
			background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#4096ee), color-stop(100%,#4096ee)); /* Chrome,Safari4+ */
			background: -webkit-linear-gradient(top, #4096ee 0%,#4096ee 100%); /* Chrome10+,Safari5.1+ */
			background: -o-linear-gradient(top, #4096ee 0%,#4096ee 100%); /* Opera 11.10+ */
			background: -ms-linear-gradient(top, #4096ee 0%,#4096ee 100%); /* IE10+ */
			background: linear-gradient(to bottom, #4096ee 0%,#4096ee 100%); /* W3C */
			filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#4096ee', endColorstr='#4096ee',GradientType=0 ); /* IE6-9 */
			color: #FFF;
		}
		.dropdown-menu > li > a:hover, .dropdown-menu > li > a:focus {
			background: #4096ee; /* Old browsers */
			background: -moz-linear-gradient(top, #4096ee 0%, #4096ee 100%); /* FF3.6+ */
			background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#4096ee), color-stop(100%,#4096ee)); /* Chrome,Safari4+ */
			background: -webkit-linear-gradient(top, #4096ee 0%,#4096ee 100%); /* Chrome10+,Safari5.1+ */
			background: -o-linear-gradient(top, #4096ee 0%,#4096ee 100%); /* Opera 11.10+ */
			background: -ms-linear-gradient(top, #4096ee 0%,#4096ee 100%); /* IE10+ */
			background: linear-gradient(to bottom, #4096ee 0%,#4096ee 100%); /* W3C */
			filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#4096ee', endColorstr='#4096ee',GradientType=0 ); /* IE6-9 */
			color: #FFF;
		}

		.espacio > .content{
			margin-left: 20px;
			padding: 10px;
			background-color: #DAC5C5;
			border-radius: 0 0 5px 5px;
		}

		.espacio > .content p { margin-bottom: 5px; }

		.espacio > .content.center{ text-align:center; }

		.modal-header {
			min-height: 16.428571429px;
			padding: 15px;
			border-bottom: 1px solid #e5e5e5;
			background-color: rgb(14, 10, 111);
			color: #FFF;
			text-shadow: #000000 0 1px;
		}

		.modal-header > h4 { font-size: 18px; }

		.right{ text-align: right; }

		.box_top.input { padding: 10px 39px 0 39px !important; }
	</style>
</head>
<body>

<!-- Start: Page Wrap -->
<div id="wrap">
	<!-- Menu: Start -->
	<?php include(__DIR__ . "/../../modules/menu.php"); ?>
	
	<!-- ==================== Tabla Costos ==================== -->
	<div class="row">
		<div class="col-lg-12">
			<!-- Box Header: Start -->
			<div class="box_top <?php if ( $cot->bloqueada == 'no' )  echo 'input'; ?> <?php echo $cot->status; ?>">
				<div class="row">
					<?php if ( $cot->bloqueada == 'si'): ?>
						<h2 style="font-size:18px;" class="icon pages"><?php echo $cot->descripcion; ?></h2>
					<?php else: ?>
						<div class="col-lg-1 col-xs-1" style="width: 40px;">
							<?php if (  !empty($id) && user_nivel($nivel=1,$con) ): ?>
								<div class="dropdown">
									<a class="cofig" href="#" data-toggle="dropdown" style="color: white;font-size: 30px;"><span class="glyphicon glyphicon-cog"></span></a>
									<ul class="status_menu dropdown-menu" role="menu" aria-labelledby="dLabel">
										<li><a class="status_requisicion <?php if ( $cot->status == 'espera' ) echo 'selected'; ?>" data-status="espera" href="#">Espera</a></li>
										<li><a class="status_requisicion <?php if ( $cot->status == 'aceptado' ) echo 'selected'; ?>" data-status="aceptado" href="#">Aceptado</a></li>
										<li><a class="status_requisicion <?php if ( $cot->status == 'cancelado' ) echo 'selected'; ?>" data-status="cancelado" href="#">Cancelar requisicion</a></li>
									</ul>
									<input type="hidden" name="status_requi" id="status_requi" value="<?php if ( !empty($id) ) echo $cot->status; ?>" >
								</div>
							<?php endif; ?>
						</div>
						<div class="col-lg-6 col-xs-6">
							<input type="text" name="descripcion" id="descripcion" value="<?php if( !empty($id) ) echo $cot->descripcion;  ?>" class="form-control validate[required]" placeholder="Descripcion de la Requisicion">
						</div>
						<div class="col-lg-4 col-xs-4 right">
							<a class="btn btn-success" data-toggle="modal" data-target="#modal_agregar">+ Agregar</a>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<!-- Box Header: End -->
			
			<!-- Box Content: Start -->
			<div class="box_content">
				<!-- Pedidos: Start -->
				<div id="pedido" class="row">
					<?php if ( !empty($id) ): ?>
						<?php 
							$b = $con->prepare("SELECT * FROM aio_requisicion_material WHERE id_requisicion=:id_requisicion");
							$b->bindParam(":id_requisicion", $id);
							$b->execute();
						?>
						<?php while( $r = $b->fetchObject() ): ?>
							<div class="col-lg-4 espacio pedido" id="1">
								<div class="titulo <?php echo $r->status; ?>">
									<div class="material"><?php echo $r->material; ?></div>
									<?php if ( $cot->bloqueada == 'no' ): ?>
										<div class="botones">
											<?php if ( user_nivel($nivel=1,$con) ): ?>
												<div class="dropdown">
													<a class="cofig" href="#" data-toggle="dropdown"><span class="glyphicon glyphicon-cog"></span></a>
													<ul class="status_menu dropdown-menu" role="menu" aria-labelledby="dLabel">
														<li><a class="status_tipo <?php if ( $r->status == 'espera' ) echo 'selected'; ?>" data-status="espera" href="#">Espera</a></li>
														<li><a class="status_tipo <?php if ( $r->status == 'aceptado' ) echo 'selected'; ?>" data-status="aceptado" href="#">Aceptado</a></li>
														<li><a class="status_tipo <?php if ( $r->status == 'cancelado' ) echo 'selected'; ?>" data-status="cancelado" href="#">Cancelado</a></li>
													</ul>
													<input type="hidden" name="status[]" class="status" value="<?php if ( !empty($id) ) echo $r->status; ?>" >
												</div>
											<?php endif; ?>
											<a class="borrar" href="#"><span class="glyphicon glyphicon-remove-sign"></span></a>
											<a class="editar" href="#"><span class="glyphicon glyphicon-edit"></span></a>
										</div>
									<?php endif; ?>
								</div>
								<div class="content">
									<p>Material: <?php echo $r->material; ?></p>
									<p>Cantidad: <?php echo $r->cantidad.' '.$r->unidad; ?></p>
									<p>P/U: $<?php echo dinero( $r->precio_unitario ); ?></p>
									<p>Total <?php echo ( $r->iva == 1.16 ) ? '(Con iva)' : '(Sin iva)' ; ?>: $<?php echo  dinero($r->precio_unitario*$r->cantidad*$r->iva);  ?></p>
									<p>Proveedor: <?php echo $r->proveedor; ?></p>
									<p>Direccion: <?php echo $r->direccion; ?></p>
									<p>Tel: <?php echo $r->telefono; ?></p>
									<p>Email: <?php echo $r->email; ?></p>
								</div>
								<?php 
									$array = array(
										'material' 		=> $r->material,
										'cantidad' 		=> $r->cantidad,
										'unidad' 		=> $r->unidad,
										'precio_unitario' 	=> $r->precio_unitario,
										'proveedor' 		=> $r->proveedor,
										'direccion' 		=> $r->direccion,
										'telefono' 		=> $r->telefono,
										'email' 			=> $r->email,
										'iva'			=> $r->iva
									);
								?>
								<input type="hidden" name="req[]" class="req" value='<?php echo json_encode($array); ?>'>
							</div>
						<?php endwhile; ?>
					<?php else: ?>				
						<div id="cero" class="col-lg-4 espacio">
							<div class="titulo">Material de la requisicion</div>
							<div class="content center">
								<a class="btn btn-success btn-xs" data-toggle="modal" data-target="#modal_agregar">Agregar Material</a></td>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<!-- ==================== Modal Agregar Costos ==================== -->
	<div id="modal_agregar" class="modal fade" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="btn btn-default close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title" id="titleAgregar">Agregar Material Requerido</h4>
				</div>
				<form id="modal_form_agregar" method="POST">
					<div class="modal-body form">
						<div class="form-group">
							<label>Material</label>
							<input type="text" name="material" id="material" class="form-control validate[required]" placeholder="Nombre del material">
						</div>
						<div class="form-group">							
							<div class="row">
								<div class="col-md-6">
									<label>Cantidad</label>
									<input type="text" name="cantidad" id="cantidad" class="form-control validate[required,custom[number]]" placeholder="Cantidad">
								</div>
								<div class="col-md-6">
									<label>Unidad</label>
									<input type="text" name="unidad" id="unidad" class="form-control validate[required]" placeholder="Unidad">
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="row">
								<div class="col-md-6">
									<label>Precio Unitario</label>
									<input type="text" name="precio_unitario" id="precio_unitario" placeholder="$" class="form-control validate[required,custom[number]]">
								</div>
								<div class="col-md-6">
									<label>Con 16% IVA</label>
									<input type="checkbox" name="iva" id="iva" class="form-control">
								</div>
							</div>
						</div>
						<div class="form-group">
							<label>Proveedor</label>
							<div class="row">
								<div class="col-md-6">
									<input type="text" name="proveedor" id="proveedor" class="form-control validate[required]" placeholder="Nombre del Proveedor">
								</div>
								<div class="col-md-6">
									<input type="text" name="direccion" id="direccion" class="form-control" placeholder="Direccion">
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="row">
								<div class="col-md-6">
									<input type="text" name="telefono" id="telefono" class="form-control validate[custom[phone]]" placeholder="Telefono">
								</div>
								<div class="col-md-6">
									<input type="text" name="email" id="email" class="form-control validate[custom[email]]" placeholder="correo@electronico.com">
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default btn-flat" data-dismiss="modal">Cancelar</button>
						<input type="hidden" name="hacer" id="hacer" value="agregar">
						<button type="submit" id="enviar" class="btn btn-primary btn-flat">+ Agregar</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	
	<!-- ==================== Informacion de la requisicion ==================== -->
	<form method="POST" id="enviar_requisicion">
		<div class="row">
			<!-- ======================== Motivo de la requisicion ========================== -->
			<div class="col-lg-6">
				<div class="row">
					<div  class="col-lg-12">
						<div>
							<div class="box_top">
								<h2 class="icon frames">Motivo</h2>
							</div>	
							<div class="box_content">
								<?php if ( $cot->bloqueada == 'si' ): ?>
									<div class="row padding">
										<div class="col-md-12">
											<p><?php echo base64_decode( $cot->motivo ); ?></p>	
										</div>
									</div>
								<?php else: ?>
									<textarea name="motivo" id="motivo" class="editor_simple" style="height: 220px; width: 412px; resize: none;">
										<?php if( !empty($id) ) echo base64_decode( $cot->motivo ); ?>
									</textarea>
								<?php endif; ?>
							</div>
						</div>
						<div>
							<?php if ( (96140 == $user_logueado->id_usuario || 12345 == $user_logueado->id_usuario || 86261 == $user_logueado->id_usuario ) && $cot->bloqueada == 'si'   ): ?>
								<div class="row">
									<div class="col-lg-12">
										<div class="box_top">
											<h2 class="icon frames">Activar PDF de la requisicion</h2>			
										</div>
										<div class="box_content padding">
											<div class="row">
												<div class="form-group">
													<div class="col-md-6">
														<input type="password" name="password" id="password" class="form-control" placeholder="Señor Eber escriba su contaseña">
													</div>
													<div class="col-md-3">
														<button type="submit" class="btn btn-md btn-success enviar" name="guardar" id="enviar_form" value="activar_pdf">Activar PDF</button>
													</div>
													<div class="col-md-3">
														<button type="submit" class="btn btn-md btn-warning enviar" name="guardar" id="bloquear" value="desactivar_pdf">Desactivar PDF</button>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>				
			</div>
			
			<!-- ===================== Informacion de la requisiscion ===================== -->
			<div class="col-lg-6">
				<div class="box_top">
					<h2 class="icon frames">Información de la requisicion</h2>
				</div>
				<div class="box_content padding">
					<?php if ( $cot->bloqueada == 'si' ): ?>
						<div class="form-group">
							<h3>Sucursal: <?php echo $cot->id_sucursal; ?></h3>
						</div>
						<div class="form-group">
							<h3>Solicita: <?php echo $cot->solicita; ?></h3>
						</div>
						<div class="form-group">
							<h3>Prioridad: <?php echo $cot->prioridad; ?></h3>
						</div>
						<div class="form-group">
							<h3>Fecha: <?php echo $cot->fecha; ?></h3>
						</div>
						<?php if ( user_nivel($nivel=1,$con) ): ?>
							<div id="controls_informacion" class="field">
								<button type="submit" class="btn btn-lg btn-warning enviar" name="guardar" id="bloquear" value="desbloquear">Desbloquear</button>
							</div>
						<?php endif; ?>
					<?php else: ?>
						<div class="field">
							<div class="form-group">
								<label>Sucursal</label>
								<select name="sucursal" id="sucursal" class="form-control validate[required]">
									<option value="">Seleccione</option>
									<?php 
										$sucursal_usuario = sucursal($_SESSION['id']);
										//Generar la seleccion del selectbox
										if ( !empty($id) ) $seleccione = $cot->id_sucursal;
										else $seleccione = $r->id_sucursal;
										//Buscar informacion de de las sucursales para desplegar									
										$b = $con->prepare("SELECT nombre,id_sucursal FROM aio_sucursal");
										$b->execute();
										while( $r = $b->fetchObject() ):
									?>
										<option value="<?php echo $r->id_sucursal; ?>" <?php if ($seleccione == $r->id_sucursal) echo 'selected="selected";' ?>>
											<?php echo $r->nombre; ?>
										</option>
									<?php endwhile; ?>
								</select>
							</div>
							<div class="form-group">
								<label>Solicita</label>
								<input type="text" name="solicita" id="solicita" placeholder="Nombre de la persona que solicita" class="form-control validate[required]" value="<?php if( !empty($id) ) echo $cot->solicita; ?>">
							</div>
							<div class="form-group">
								<label>Prioridad</label>
								<select name="prioridad" id="prioridad" class="form-control validate[required]">
									<option <?php if( !empty($id) && $cot->prioridad == '') echo "Selected"; ?> value="">Seleccione</option>
									<option <?php if( !empty($id) && $cot->prioridad == 'bajo') echo "Selected"; ?> value="bajo">Bajo</option>
									<option <?php if( !empty($id) && $cot->prioridad == 'medio') echo "Selected"; ?> value="medio">Medio</option>
									<option <?php if( !empty($id) && $cot->prioridad == 'alto') echo "Selected"; ?> value="alto">Alto</option>
								</select>
							</div>
							<div class="form-group">
								<label>Fecha</label>
								<div class="input-group">
									<input type="text" name="fecha" id="fecha" value="<?php if ( !empty($id) ) echo $cot->fecha; ?>" class="form-control date validate[required]">
									<span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
								</div>
							</div>
						</div>
						<div id="controls_informacion" class="field">
							<?php if ( !empty($id) && user_nivel($nivel=1,$con) ): ?>
								<button type="submit" class="btn btn-lg btn-success enviar" name="guardar" id="enviar_form" value="editar_status">Guardar</button>
								<button type="submit" class="btn btn-lg btn-warning enviar" name="guardar" id="bloquear" value="bloquear">Guardar y Bloquear</button>
							<?php elseif ( !empty($id) ): ?>
								<button type="submit" class="btn btn-lg btn-success enviar" name="guardar" id="enviar_form" value="editar">Guardar</button>
							<?php else: ?>
								<button type="submit" class="btn btn-lg btn-success enviar" name="guardar" id="enviar_form" value="guardar">Guardar</button>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<input type="hidden" name="id_requisicion" id="id_requisicion" value="<?php if ( !empty($id) ) echo $id; ?>">
				</div>
			</div>
		</div>
	</form>

	<!-- Footer Grid: Start -->
	<?php include(__DIR__ . "/../../modules/pie.php"); ?>
</div>
<!-- End: Page Wrap -->

<!--==================== Le jquery ====================-->
<?php include(__DIR__ . "/../../modules/js.php"); ?>
<script type="text/javascript" src="<?php echo _BASE_URL; ?>/assets/js/gritter/jquery.gritter.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.3/moment.min.js"></script>
<script src="<?php echo _BASE_URL; ?>/assets/js/customrequiest.js"></script>
<script src="<?php echo _BASE_URL; ?>/assets/js/jquery.confirm.min.js"></script>
<script src="<?php echo _BASE_URL; ?>/assets/js/jquery.tabletojson.js"></script>


<script type="text/javascript">
	$(document).ready(function() {
		/*===========================================================*/
		/*	Function mensaje de exito
		/*===========================================================*/
		function mensaje(data){
			$.gritter.add({
				title: 'Exito',
				text: data.mensaje,
				class_name: 'success',
				time: 80000
			});
		}

		<?php if ( $cot->bloqueada == 'si'): ?>
			/*===========================================================*/
			/*	Enviar y guardar la cotizacion
			/*===========================================================*/
			$('#enviar_requisicion').validationEngine("attach", {
				promptPosition : "bottomLeft", 
				autoPositionUpdate : true,
				onValidationComplete: function(form, status){
					if ( status == true ){
						//Serializar la informacion del form
						var form = $('#enviar_requisicion').serializeObject();

						//Boton hacer
						form.hacer = $("#enviar_form").val();

						//Enviar por ajax
						$.customRequest(
							'hacer',
							form,
							{
								onSuccess: function(result) {
									mensaje(result);
									if ( result.hacer == "desbloquear" ){
										$(location).attr('href','agregar.php?id='+result.id_requisicion);
									}else if ( result.hacer == 'activar_pdf' ){
										window.open(result.archivo);
									}
								}
							}
						);
					}

				}//onValidationComplete: function(form, status)		
			});

		<?php else: ?>
			/*===========================================================*/
			/*	Function estatus de cada una de los materiales
			/*===========================================================*/
			$(document).on('click', '.status_tipo', function(event) {
				event.preventDefault();
				//Obtenemos el valor data-status del dropdown
				var status = $(this).data('status');
				//Seleccionamos el titulo
				var titulo = $(this).parents('.titulo');
				//Agregamos a la opcion seleccionada la clase selected
				$(this).addClass('selected');
				//Le quitamos a las demas opciones el valor de selected
				$(this).parent().siblings().children().removeClass('selected');
				//Pasamos el varlo de Status al input
				$(this).parent().parent().siblings('.status').val(status);
				//Se cambia de clase el titulo para darle el color necesario
				switch (status){
					case 'aceptado':
						titulo.attr('class', 'titulo aceptado');
					break;

					case 'cancelado':
						titulo.attr('class', 'titulo cancelado');
					break;

					default:
						titulo.attr('class', 'titulo');
					break;
				}
			});

			/*===========================================================*/
			/*	Function estatus de la requisicion completa
			/*===========================================================*/
			$(document).on('click', '.status_requisicion', function(event) {
				event.preventDefault();
				//Obtenemos el valor data-status del dropdown
				var status = $(this).data('status');
				//Seleccionamos el titulo
				var titulo = $(this).parents('.input');
				//Agregamos a la opcion seleccionada la clase selected
				$(this).addClass('selected');
				//Le quitamos a las demas opciones el valor de selected
				$(this).parent().siblings().children().removeClass('selected');
				//Pasamos el varlo de Status al input
				$('#status_requi').val(status);
				//Se cambia de clase el titulo para darle el color necesario
				switch (status){
					case 'aceptado':
						titulo.attr('class', 'box_top input aceptado');
					break;

					case 'cancelado':
						titulo.attr('class', 'box_top input cancelado');
					break;

					default:
						titulo.attr('class', 'box_top input');
					break;
				}
			});


			/*===========================================================*/
			/*	Enviar y guardar la cotizacion
			/*===========================================================*/
			$('#enviar_requisicion').validationEngine("attach", {
				promptPosition : "bottomLeft", 
				autoPositionUpdate : true,
				onValidationComplete: function(form, status){
					if ( status == true ){
						if ( $('#descripcion').val() != null ){
							if ( $('#cero').length == false ) {
								//Boton hacer
								var hacer = { 'hacer':$("#enviar_requisicion").context.activeElement.value };
								//Serializar la informacion del form
								var descripcion = $('#descripcion').serializeObject();
								var form = $('#enviar_requisicion').serializeObject();
								var pedido = $('#pedido .req').serializeObject();
								var requi_status = $('#status_requi').serializeObject();
								var requi_status_material = $('#pedido .status').serializeObject();							
								//Se extiendo el obejto pedido en form, y se concatenan en una sola variable
								$.extend(form, hacer);
								$.extend(form, requi_status);
								$.extend(form,requi_status_material);
								$.extend(form, descripcion);
								$.extend(form, pedido);
								//Enviar por ajax
								$.customRequest('hacer', form, {
									onSuccess: function(result) {
										mensaje(result);
										if ( result.hacer == "bloquear" ){
											$(location).attr('href','agregar.php?id='+result.id_requisicion);
										}else if ( result.hacer == "editar" ){
											$(location).attr('href','/manage_requisicion/table.php');
										}else{
											$('#enviar_requisicion #guardar').replaceWith(
												$('<button />',{ 'type':'submit', 'class':'btn btn-lg btn-info enviar', 'name':'guardar', 'value':result.hacer, 'text':'Guardar'  })
											);

											//Regrasa la id de la requisicion resien guardada
											$('#enviar_requisicion #id_requisicion').val(result.id_requisicion);
											//Mensaje de exito
										}
									}
								});
							}else alert('No se ah agregado nada a la requisicion');
						}
					}

				}//onValidationComplete: function(form, status)		
			});

			/*===========================================================*/
			/*	Agregar a la cotizacion
			/*===========================================================*/
			///////////////////////////////////////////////////////////////////
			//Agregar con validationengine
			var j = 1;
			$('#modal_form_agregar').validationEngine("attach", {
				promptPosition : "bottomLeft", 
				autoPositionUpdate : true,
				onValidationComplete: function(form, status){
					if ( status == true ){
						//Serializar la informacion del form
						var datos = $("#modal_form_agregar").serializeObject();
						//Se trata de manera especial el checbox
						if ( $('#modal_form_agregar #iva').prop('checked') ){ 
							var iva = 1.16;
							var text_iva = ' (Con iva)';
						}else{
							var iva = 1;
							var text_iva = ' (Sin iva)';
						}
						var hacer = $('#modal_form_agregar #hacer').val();
						var costo = parseFloat( datos.cantidad ) * parseFloat( datos.precio_unitario )*iva;
						//Extender la clase
						$.extend( datos, { 'iva':iva} );

						//Agregar a la pagina
						if ( $('#cero').length == 1 ) $('#cero').remove(); //Comprobar si se quito row dommie

						switch ( hacer ){
							case 'agregar':
								$('#pedido').append(
									$('<div/>', {'class': 'col-lg-4 espacio pedido', 'id':j}).append(
										$('<div/>', {'class': 'titulo'}).append(
											$('<div/>', {'class':'material', text: datos.material}),
											$('<div/>', {'class': 'botones'}).append(
												<?php if ( user_nivel($nivel=1,$con) ): ?>
													$('<div />',{'class':'dropdown'}).append(
														$('<a />',{ 'class':'cofig', 'href':'#', 'data-toggle':'dropdown' }).append(
															$('<span />', { 'class':'glyphicon glyphicon-cog' })
														),
														$('<ul />',{ 'class':'status_menu dropdown-menu', 'role':'menu', 'aria-labelledby':'dLabel' }).append(
															$('<li />').append( $('<a />', { 'class':'status_tipo selected', 'data-status':'espera', 'text':'Espera'} ) ),
															$('<li />').append( $('<a />', { 'class':'status_tipo', 'data-status':'aceptado', 'text':'Aceptado'} ) ),
															$('<li />').append( $('<a />', { 'class':'status_tipo', 'data-status':'cancelado', 'text':'Cancelado'} ) )
														),
														$('<input />', { 'type':'hidden', 'name':'status[]', 'class':'status'  })
													),
												<?php endif; ?>
												$('<a />', {'class':'borrar', 'href':'#'})
												.append(
													$('<span />',{ 'class': 'glyphicon glyphicon-remove-sign'})
												),
												$('<a />', {'class':'editar', 'href':'#'})
												.append(
													$('<span />',{ 'class': 'glyphicon glyphicon-edit'})
												)
											)
										)
									)
									.append(
										$('<div/>', {'class': 'content'}).append(
											$('<p/>', {'text':'Material: '+datos.material}),
											$('<p/>', {'text': 'Cantidad: '+datos.cantidad+' '+datos.unidad}),
											$('<p/>', {'text': 'P/U: $'+datos.precio_unitario}),
											$('<p/>', {'text': 'Total'+text_iva+': $'+costo}),
											$('<p/>', {'text': 'Proveedor: '+datos.proveedor}),
											$('<p/>', {'text': 'Direccion: '+datos.direccion}),
											$('<p/>', {'text': 'Tel: '+datos.telefono}),
											$('<p/>', {'text': 'Email: '+datos.email})
										)
									)
									.append(
										$('<input />',{ 'type':'hidden' ,'name':'req[]', 'class':'req' ,'value': JSON.stringify(datos) })
									)
								);
								j = j+1;
							break;

							case 'editar':
								var find_pedido = $('#pedido').find('.editando');
								find_pedido.replaceWith(
									$('<div/>', {'class': 'col-lg-4 espacio pedido', 'id':j}).append(
										$('<div/>', {'class': 'titulo'}).append(
											$('<div/>', {'class':'material', text: datos.material}),
											$('<div/>', {'class': 'botones'}).append(
												<?php if ( user_nivel($nivel=1,$con) ): ?>
													$('<div />',{'class':'dropdown'}).append(
														$('<a />',{ 'class':'cofig', 'href':'#', 'data-toggle':'dropdown' }).append(
															$('<span />', { 'class':'glyphicon glyphicon-cog' })
														),
														$('<ul />',{ 'class':'status_menu dropdown-menu', 'role':'menu', 'aria-labelledby':'dLabel' }).append(
															$('<li />').append( $('<a />', { 'class':'status_tipo selected', 'data-status':'espera', 'text':'Espera'} ) ),
															$('<li />').append( $('<a />', { 'class':'status_tipo', 'data-status':'aceptado', 'text':'Aceptado'} ) ),
															$('<li />').append( $('<a />', { 'class':'status_tipo', 'data-status':'cancelado', 'text':'Cancelado'} ) )
														),
														$('<input />', { 'type':'hidden', 'name':'status[]', 'class':'status'  })
													),
												<?php endif; ?>
												$('<a />', {'class':'borrar', 'href':'#'})
												.append(
													$('<span />',{ 'class': 'glyphicon glyphicon-remove-sign'})
												),
												$('<a />', {'class':'editar', 'href':'#'})
												.append(
													$('<span />',{ 'class': 'glyphicon glyphicon-edit'})
												)
											)
										)
									)
									.append(
										$('<div/>', {'class': 'content'}).append(
											$('<p/>', {'text':'Material: '+datos.material}),
											$('<p/>', {'text': 'Cantidad: '+datos.cantidad+' '+datos.unidad}),
											$('<p/>', {'text': 'P/U: $'+datos.precio_unitario}),
											$('<p/>', {'text': 'Total'+text_iva+': $'+costo}),
											$('<p/>', {'text': 'Proveedor: '+datos.proveedor}),
											$('<p/>', {'text': 'Direccion: '+datos.direccion}),
											$('<p/>', {'text': 'Tel: '+datos.telefono}),
											$('<p/>', {'text': 'Email: '+datos.email})
										)
									)
									.append(
										$('<input />',{ 'type':'hidden' ,'name':'req[]','class':'req' ,'value': JSON.stringify(datos) })
									)
								);
							break;
						}

						$('#modal_agregar').modal('hide');
					}//if ( status == true )

				}//onValidationComplete: function(form, status)		
			});

			////////////////////////////////////////////////////////////////////
			//Borrar
			$(document).on('click', '.borrar', function(event) {
				event.preventDefault();
				var req = $(this).parent().parent().parent();
				$.confirm({
					text: "Esta seguro que quiere quitar este elemento de la requisicion",
					title: "Confirmar",
					confirm: function(button) {
						req.remove();
						if( $('#pedido > div.pedido').length == 0 ){
							$('#pedido').append(
								$('<div/>', {'class': 'col-lg-4 espacio', 'id':'cero'}).append(
									$('<div/>', {'class': 'titulo','text': 'Material de la requisicion'})
								)
								.append(
									$('<div/>', {'class': 'content center'}).append(
										$('<button/>',{ 'class':'btn btn-success btn-xs', 'data-toggle': 'modal', 'data-target':'#modal_agregar','text':'Agregar Material' })
									)
								)
							);	
						}
					},
					cancel: function(button) {},
					confirmButton: "Borrar",
					cancelButton: "Cancelar",
					post: true
				});
			});

			////////////////////////////////////////////////////////////////////
			//Editar
			$(document).on('click', '.editar', function(event) {
				event.preventDefault();
				var div = $(this).parent().parent().parent();
				div .addClass('editando');			
				var req = eval( "(" +div.find(':input').val()+ ")" );
				//IVA
				if ( req.iva == 1.16 ) $('#modal_agregar #iva').prop('checked', true);
				else $('#modal_agregar #iva').prop('checked', false);
				$('#modal_agregar #material').val( req.material );
				$('#modal_agregar #cantidad').val( req.cantidad );
				$('#modal_agregar #unidad').val( req.unidad );
				$('#modal_agregar #precio_unitario').val( req.precio_unitario );
				$('#modal_agregar #proveedor').val( req.proveedor );
				$('#modal_agregar #direccion').val( req.direccion );
				$('#modal_agregar #telefono').val( req.telefono );
				$('#modal_agregar #email').val( req.email );
				$('#modal_agregar #hacer').val('editar');
				$('#modal_agregar #enviar').text('Editar').button("refresh");
				$('#modal_agregar').modal('show');
			});

			////////////////////////////////////////////////////////////////////	
			//Al cerrar el modal
			$('#modal_agregar').on('hidden.bs.modal', function (e) {
				$("#modal_form_agregar").trigger("reset");			
				$('#modal_agregar #titleAgregar').html('Agregar a la cotización');
				$('#modal_agregar #enviar').text('+Agregar').button("refresh");
				$('#modal_form_agregar #hacer').val('agregar');
				$('#modal_form_agregar').validationEngine('hideAll');
				$('#pedido').find('.editando').removeClass('editando');
			});

		<?php endif; ?>
	});
</script>

</body>

</html>
