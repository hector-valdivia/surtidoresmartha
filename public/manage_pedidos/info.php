<?php 	
	session_start();
	include(__DIR__ . "/../../funciones.php");
	registrado();

	//Conexion de BD
	$con = conecta();

	$info = limpiar( desencriptar($_GET['id']) ); 

	if ( empty($info) ) {
		header("location: table.php");
	}

	$b = $con->prepare("SELECT * FROM aio_orden WHERE folio=:id");
	$b->bindParam(':id',$info);
	$b->execute();
	$ped = $b->fetchObject();

	$cliente  = info_cliente($ped->id_cliente);	
	$sucursal = info_sucursal($ped->sucursal);	
?>

<!DOCTYPE HTML>
<html lang="es">
<head>
	<?php include(__DIR__ . "/../../modules/header.php"); ?>	
	<title>Pedido <?php echo $info; ?></title>
	<script src="<?php echo _BASE_URL; ?>/assets/js/chained.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			$("#categoria").chained("#trabajador");
		});
	</script>

	<style type="text/css">
		.box_content h3 {
			font-size: 1.5em;
			padding: 10px 10px 12px 10px;
			margin: 0px 0 14px 0;
		}
	</style>
</head>
<body>

<!-- Start: Page Wrap -->
<div id="wrap" class="container_24">

	<!-- Menu: Start -->	
	<?php include(__DIR__ . "/../../modules/menu.php"); ?>
	<!-- Menu: End -->
	<div class="row">
		<div class="col-lg-6">
			<div class="box_top">
				<h2 class="icon cabin">Info. Pedido</h2>
			</div>
			<div class="box_content">
				<table>
					<tr>
						<td class="align_left"><span class="icon support"></span><b>Folio</b></td>
						<td class="align_left green"><?php echo $ped->folio; ?></td>
					</tr>
					<tr>
						<td class="align_left"><span class="icon home"></span><b>Sucursal</b></td>
						<td class="align_left"><?php echo $sucursal->nombre; ?></td>
					</tr>
					<tr>
						<td class="align_left"><span class="icon date"></span><b>Fecha Deseada</b></td>
						<td class="align_left"><?php echo $ped->fecha_deseada; ?></td>
					</tr>
					<tr>
						<td class="align_left"><span class="icon date"></span><b>Fecha Orden</b></td>
						<td class="align_left"><?php echo $ped->fecha_orden; ?></td>
					</tr>				
					<tr>
						<td class="align_left"><span class="icon loading"></span><b>Estado</b></td>
						<td class="align_left"><?php echo $ped->estado_orden; ?></td>
					</tr>
				</table>	
			</div>
		</div>

		<div class="col-lg-6">
			<div class="box_top">
				<h2 class="icon user">Cliente</h2>
			</div>
			<div class="box_content">
				<table>
					<tr>
						<td class="align_left"><span class="icon users"></span><b>Nombre / ID </b></td>
						<td class="align_left"><?php echo nombre_cliente($ped->id_cliente); ?> / <?php echo $ped->id_cliente; ?></td>
					</tr>
					<tr>
						<td class="align_left"><span class="icon mail"></span><b>Email</b></td>
						<td class="align_left"><?php echo $cliente->email; ?></td>
					</tr>
					<tr>
						<td class="align_left"><span class="icon contact"></span><b>Tel / Cel</b></td>
						<td class="align_left"><?php echo $cliente->telefono.' / '.$cliente->celular; ?></td>
					</tr>
					<tr>
						<td class="align_left"><span class="icon companys"></span><b>Razon social</b></td>
						<td class="align_left">
							<?php if ( !empty($cliente->razon_social) ): ?>
								<?php echo $cliente->razon_social; ?>
							<?php else: ?>
								No se encuentra este registro
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td class="align_left"><span class="icon companys"></span><b>RFC</b></td>
						<td class="align_left">
							<?php if ( !empty($cliente->rfc) ): ?>
								<?php echo $cliente->rfc; ?>
							<?php else: ?>
								No se encuentra este registro
							<?php endif; ?>
						</td>
					</tr>
				</table>	
			</div>
		</div>

		<div class="col-lg-8">
			<!-- Box Header: Start -->
			<div class="box_top">
				<?php 
				    $c = $con->prepare("SELECT COUNT(id) FROM aio_orden_personal WHERE folio=:folio");
				    $c->bindParam(':folio',$ped->folio);
				    $c->execute();
				    $numero = $c->fetchColumn();
				?>
				<h2 class="icon pages">Costo de la Orden de trabajo</h2>		
			</div>
			<!-- Box Header: End -->
			
			<!-- Box Content: Start -->
			<div class="box_content">
				<!-- Pedidos: Start -->
				<div id="pedido">
					<table>
						<thead>
							<tr>
								<th class="align_left">Empleado</th>
								<th class="align_left center">Categoria</th>
								<th class="align_left center">Dia</th>
								<th class="align_left center">Total</th>
								<th class="align_left center">Salario</th>
								<th class="align_left center">Costo</th>
							</tr>
						</thead>
						<tbody class="content">
							<?php
								//Inicializar total del costo
								$total_costo = 0;

								//Busqueda del personal
								$b = $con->prepare("SELECT * FROM aio_orden_personal WHERE folio=:folio");
								$b->bindParam(':folio', $ped->folio);
								$b->execute();
							?>
							<?php while ( $r = $b->fetchObject() ): ?>
								<?php $info_personal = info_personal($r->id_personal); ?>
								<tr>
									<td class="align_left"><?php echo nombre_personal($r->id_personal); ?></td>
									<td class="align_left center"><?php echo $r->categoria ?></td>
									<td class="align_left center"><?php echo $r->dia; ?></td>
									<td class="align_left center"><?php echo $r->horas;?></td>
									<td class="align_left center">$<?php echo dinero($info_personal->salario); ?></td>
									<td class="align_left center">$<?php echo $r->costo; $total_costo+=$r->costo;  ?></td>
								</tr>
							<?php endwhile; ?>		
						</tbody>
						<tfoot>
							<tr>
								<td colspan="5" style="text-align:right;"><b>Total neto:</b></td>
								<td class="align_left center">$<?php echo dinero($total_costo); ?></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>

		<!-- Form aticulos-->
		<div class="col-lg-4">
			<div class="box_top">
				<h2 class="icon frames">Agregar trabajadores</h2>			
			</div>

			<div class="box_content padding">
				<form action="enviar.php" name="trabajadores" id="trabajadores" class="validar" method="post" enctype="multipart/form-data">
					<div class="field">
						<label class="left">Trabajador</label>
						<select name="trabajador" id="trabajador" class="form-control validate[required]">
							<option value="" selected="true">Seleccione</option>
							<?php
								$b = $con->prepare("SELECT id_usuario,nombre,apellido,categoria FROM aio_personal WHERE id!=1");
								$b->execute();						
								while ( $r = $b->fetchObject() ){

									if ( !empty($r->categoria) ) {

										echo '<option value="'.$r->id_usuario.'">'.$r->apellido.' '.$r->nombre.'</option>';

										$categoria = explode(',',$r->categoria);
										foreach ($categoria as $cat) {
											$opciones.= '<option value="'.$cat.'" class="'.$r->id_usuario.'">'.$cat.'</option>';
										}
									}
									
								}
							?>
						</select>
						<select name="categoria" id="categoria" class="form-control validate[required]">
							<option value="" selected="true">Seleccione</option>
							<?php echo $opciones; ?>
						</select>
					</div>			

					<div class="field">
						<label class="left">Dia</label>
						<input type="text" name="dia" id="dia" class="form-control date validate[required]" />
					</div>				
					<div class="field">
						<label class="left">Horas trabajadas</label>
						<input type="text" name="horas" id="horas" class="form-control validate[required]" />
					</div>

					<div class="field">
						<input type="text" name="folio" id="folio" value="<?php echo $ped->folio; ?>" readonly="true" class="form-control small validate[required] text-input" style="display:none;" />
						<input type="text" name="hacer" id="hacer" value="trabajador" readonly="true" class="form-control small validate[required] text-input" style="display:none;" />
						<button class="btn btn-success">Guardar</button>
					</div>
				</form>
			</div>
		</div>	

		<div class="col-lg-6">
			<!-- Box Header: Start -->
			<div class="box_top">
				<?php
				    $c = $con->prepare("SELECT COUNT(id) FROM aio_orden_material WHERE folio=:folio");
				    $c->bindParam(':folio',$ped->folio);
				    $c->execute();
				    $numero = $c->fetchColumn();
				?>
				<h2 class="icon pages">Material</h2>		
			</div>
			<!-- Box Header: End -->
			
			<!-- Box Content: Start -->
			<div class="box_content">		
				<!-- Pedidos: Start -->
				<div id="pedido">
					<table>
						<thead>
							<tr>
								<th class="align_left">Material</th>
								<th class="align_left">Cantidad</th>
								<th class="align_left center">Costo</th>
							</tr>
						</thead>
						<tbody class="content">
							<?php
								//Inicializar total de material
								$total_material = '';

								//Busqueda del material usado
								$b = $con->prepare("SELECT * FROM aio_orden_material WHERE folio=:folio");
								$b->bindParam(':folio', $ped->folio);
								$b->execute();
							?>
							<?php while ( $r = $b->fetchObject() ): ?>
								<tr>
									<td class="align_left"><?php echo echo_limpiar( $r->material ); ?></td>
									<td class="align_left"><?php echo echo_limpiar( $r->cantidad ).' '.echo_limpiar( $r->unidad ); ?></td>
									<td class="align_left center">$<?php echo dinero($r->costo); $total_material+=$r->costo; ?></td>
								</tr>
							<?php endwhile; ?>
						</tbody>
						<tfoot>
							<tr>
								<td style="text-align:right;" colspan="2"><b>Total neto:</b></td>
								<td class="align_left center">$<?php if ( !empty($total_material) ) echo dinero($total_material); ?></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>

		<div class="col-lg-6">
			<div class="box_top">
				<h2 class="icon frames">Agregar Material</h2>			
			</div>	

			<div class="box_content padding">
				<form action="enviar.php" name="material" id="material" class="validar" method="post" enctype="multipart/form-data">
					<div class="field">
						<label class="left">Material</label>
						<input type="text" name="material" id="material" class="form-control validate[required]" placeholder="Material" />
					</div>			
					<div class="field">
						<label>Cantidad</label>
						<div class="row">
							<div class="col-lg-6">
								<input type="text" name="cantidad" id="cantidad" class="form-control validate[required]" placeholder="Cantidad" />	
							</div>
							<div class="col-lg-6">
								<input type="text" name="unidad" id="unidad" class="form-control validate[required]" placeholder="Unidad" />
							</div>
						</div>
					</div>
					<div class="field">
						<label class="left">Costo</label>
						<input type="text" name="costo" id="costo" class="form-control validate[required]" placeholder="$" />
					</div>
					<div class="field">
						<input type="text" name="folio" id="folio" value="<?php echo $ped->folio; ?>" readonly="true" class="small form-control validate[required] text-input" style="display:none;" />
						<input type="text" name="hacer" id="hacer" value="material" readonly="true" class="small form-control validate[required] text-input" style="display:none;" />
						<button class="btn btn-success">Guardar</button>
					</div>
				</form>
			</div>
		</div>	

		<div class="col-lg-6">
			<!-- Box Header: Start -->
			<div class="box_top">
				<?php 
				    $c = $con->prepare("SELECT COUNT(id) FROM aio_orden_material WHERE folio=:folio");
				    $c->bindParam(':folio',$ped->folio);
				    $c->execute();
				    $numero = $c->fetchColumn();
				?>
				<h2 class="icon pages">Herramienta Utilizada</h2>		
			</div>
			<!-- Box Header: End -->
			
			<!-- Box Content: Start -->
			<div class="box_content">		
				<!-- Pedidos: Start -->
				<div id="pedido">
					<table>
						<thead>
							<tr>
								<th class="align_left">Herramienta</th>
								<th class="align_left center">Cantidad</th>
							</tr>
						</thead>
						<tbody class="content">
							<?php							
								$b = $con->prepare("SELECT * FROM aio_orden_herramienta WHERE folio=:folio");
								$b->bindParam(':folio', $ped->folio);
								$b->execute();
							?>
							<?php while ( $r = $b->fetchObject() ): ?>
								<tr>
									<td class="align_left"><?php echo echo_limpiar( $r->herramienta ); ?></td>
									<td class="align_left center"><?php echo $r->cantidad; ?></td>
								</tr>
							<?php endwhile; ?>		
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<div class="col-lg-6">
			<div class="box_top">
				<h2 class="icon frames">Agregar Herramienta</h2>			
			</div>	

			<div class="box_content padding">
				<form action="enviar.php" name="material" id="material" class="validar" method="post" enctype="multipart/form-data">
					<div class="field">
						<label class="left">Herramienta</label>
						<input type="text" name="herramienta" id="herramienta" class="form-control validate[required]" />
					</div>			

					<div class="field">
						<label class="left">Cantidad</label>
						<input type="text" name="cantidad" id="cantidad" class="form-control validate[required]" />
					</div>
					<div class="field">
						<input type="text" name="folio" id="folio" value="<?php echo $ped->folio; ?>" readonly="true" class="small validate[required] text-input" style="display:none;" />
						<input type="text" name="hacer" id="hacer" value="herramienta" readonly="true" class="small validate[required] text-input" style="display:none;" />
						<button class="btn btn-success">Guardar</button>
					</div>
				</form>
			</div>
		</div>	

	</div>

	<form action="enviar" method="post" name="estado_pedido" id="estado_pedido" class="validar">
		<div class="row">
			<div class="col-lg-6" >
				<div class="box_top">
					<h2 class="icon frames">Trabajo Solicitado</h2>
				</div>	
				<div class="box_content">
					<textarea name="trabajo_solicitado" id="trabajo_solicitado" class="validate[required] editor_simple" style="height: 220px; width: 412px; resize: none;">
						<?php echo $ped->trabajo_solicitado; ?>
					</textarea>
				</div>
			</div>

			<div class="col-lg-6" >
				<div class="box_top">
					<h2 class="icon frames">Trabajo a Realizar</h2>
				</div>	
				<div class="box_content">
					<textarea name="trabajo_realizar" id="trabajo_realizar" class="validate[required] editor_simple" style="height: 220px; width: 412px; resize: none;">
						<?php echo $ped->trabajo_realizar; ?>
					</textarea>
				</div>
			</div>

			<div class="col-lg-6" >
				<div class="box_top">
					<h2 class="icon frames">Diagnostico de la falla y observaciones</h2>
				</div>	
				<div class="box_content">
					<textarea name="diagnostico_observaciones" id="diagnostico_observaciones" class="validate[required] editor_simple" style="height: 220px; width: 412px; resize: none;">
						<?php echo $ped->diagnostico_observaciones; ?>
					</textarea>
				</div>
			</div>	

			<!-- Form aticulos-->
			<div class="col-lg-6">
				<div class="box_top">
					<h2 class="icon frames">Acciones sobre la orden</h2>			
				</div>
				<div class="box_content padding">				
					<div class="field">
						<label>Cambiar estado del pedido</label>
						<select name="estado_orden" id="estado_orden" class="form-control">
							<option <?php if($ped->estado_orden == 'abierto') echo "selected=selected"; ?> value="abierto">Abierto</option>
							<option <?php if($ped->estado_orden == 'vencido') echo "selected=selected"; ?> value="vencido">Vencido</option>
							<option <?php if($ped->estado_orden == 'cancelado') echo "selected=selected"; ?> value="cancelado">Cancelado</option>
							<option <?php if($ped->estado_orden == 'terminado') echo "selected=selected"; ?> value="terminado">Terminado</option>
						</select>
					</div>
					<div class="field">
						<input type="hidden" name="hacer" id="hacer" value="info_pedido" class="validate[required]" />
						<input type="hidden" name="folio" id="folio" value="<?php echo $ped->folio; ?>" class="validate[required]" />					
						<button class="btn btn-success">Enviar</button>
					</div>				
				</div>
			</div>
		</div>
	</form>
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
