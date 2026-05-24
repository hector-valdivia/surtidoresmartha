<?php 	
	session_start();
	include('../funciones.php');
	registrado();

	//Conexion de BD
	$con = conecta();


	//Limpiar y desencriptar la bariable que llega desde get
	$planeador = limpiar( desencriptar($_SESSION['id']) );
	$folio     = limpiar( desencriptar($_GET['id']) );

	$c = $con->prepare("SELECT COUNT(id) FROM aio_orden WHERE planeador=:id");
	$c->bindParam(':id',$planeador);
	$c->execute();

	if ( $c->fetchColumn() == 0 ) {
		header("location:table.php");
	}else{
		$b = $con->prepare("SELECT * FROM aio_orden WHERE folio=:folio");
		$b->bindParam(':folio',$folio);
		$b->execute();
		$orden = $b->fetchObject();
	}

?>

<!DOCTYPE HTML>
<html lang="es">
<head>
	<?php include('../modules/header.php'); ?>	
	<title>Orden <?php echo $folio; ?></title>
</head>
<body>

<!-- Start: Page Wrap -->
<div id="wrap" class="container_24">

	<!-- Menu: Start -->	
	<?php include('../modules/menu_taller.php'); ?>
	<!-- Menu: End -->

	<div class="grid_24">
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
						</tr>
					</thead>
					<tbody class="content">
						<?php							
							$b = $con->prepare("SELECT * FROM aio_orden_personal WHERE folio=:folio");
							$b->bindParam(':folio', $folio);
							$b->execute();
						?>
						<?php while ( $r = $b->fetchObject() ): ?>
							<?php $info_personal = info_personal($r->id_personal); ?>
							<tr>
								<td class="align_left"><?php echo nombre_personal($r->id_personal); ?></td>
								<td class="align_left center"><?php echo $r->categoria ?></td>
								<td class="align_left center"><?php echo $r->dia; ?></td>
								<td class="align_left center"><?php echo $r->horas;?></td>
							</tr>
						<?php endwhile; ?>		
					</tbody>
				</table>
			</div>
		</div>
	</div>


	<!-- Form aticulos-->
	<div class="grid_24">
		<div class="box_top">
			<h2 class="icon frames">Editar informacion de la orden <i><?php echo $folio; ?></i></h2>			
		</div>
	<script type="text/javascript" src="<?php echo _BASE_URL; ?>/js/chained.js"></script>

	<script type="text/javascript">
		$(window).load(function(){
			$("div #uniform-trabajador.selector").hide();
			$("div #uniform-categoria.selector").hide();
		});

		$(document).ready(function(){
			$("#trabajador").chained("#categoria");
		});
	 </script>		

		<div class="box_content padding">
			<form action="enviar.php" name="trabajadores" id="trabajadores" class="validar" method="post" enctype="multipart/form-data">

				<div class="field">
					<label class="left">Trabajador</label>
					<select name="trabajador" id="trabajador" class="validate[required]">
						<option value="" selected="true">Seleccione</option>
						<?php
							$b = $con->prepare("SELECT id_usuario,nombre,apellido,categoria FROM aio_personal WHERE id_usuario!=:planeador AND id!=1");
							$b->bindParam(':planeador',$planeador);
							$b->execute();						
							while ( $r = $b->fetchObject() ){

								if ( !empty($r->categoria) ) {

									echo '<option value="'.$r->id_usuario.'">'.$r->nombre.' '.$r->apellido.'</option>';

									$categoria = explode(',',$r->categoria);
									foreach ($categoria as $cat) {
										$opciones.= '<option value="'.$cat.'" class="'.$r->id_usuario.'">'.$cat.'</option>';
									}
								}
								
							}
						?>
					</select>
					<select name="categoria" id="categoria" class="validate[required]">
						<option value="" selected="true">Seleccione</option>
						<?php echo $opciones; ?>
					</select>
				</div>			

				<div class="field">
					<label class="left">Dia</label>
					<input type="text" name="dia" id="dia" class="date validate[required]" />
				</div>				
				<div class="field">
					<label class="left">Horas trabajadas</label>
					<input type="text" name="horas" id="horas" class="validate[required]" />
				</div>

				<div class="field">
					<input type="text" name="folio" id="folio" value="<?php echo $folio; ?>" readonly="true" class="small validate[required] text-input" style="display:none;" />
					<input type="text" name="hacer" id="hacer" value="editar" readonly="true" class="small validate[required] text-input" style="display:none;" />
					<button>Guardar</button>
				</div>
			</form>
		</div>
	</div>

	<div class="grid_24">
		<!-- Box Header: Start -->
		<div class="box_top">
			<?php 
			    $c = $con->prepare("SELECT COUNT(id) FROM aio_orden_material WHERE folio=:folio");
			    $c->bindParam(':folio',$folio);
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
							<th class="align_left center">Costo</th>
						</tr>
					</thead>
					<tbody class="content">
						<?php							
							$b = $con->prepare("SELECT * FROM aio_orden_material WHERE folio=:folio");
							$b->bindParam(':folio', $folio);
							$b->execute();
						?>
						<?php while ( $r = $b->fetchObject() ): ?>
							<tr>
								<td class="align_left"><?php echo $r->material; ?></td>
								<td class="align_left center">$<?php echo dinero($r->costo); $total_material+=$r->costo; ?></td>
							</tr>
						<?php endwhile; ?>		
					</tbody>
					<tfoot>
						<tr>
							<td style="text-align:right;"><b>Total neto:</b></td>
							<td class="align_left center">$<?php echo dinero($total_material); ?></td>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>


	<div class="grid_24">
		<div class="box_top">
			<h2 class="icon frames">Agregar Material</h2>			
		</div>	

		<div class="box_content padding">
			<form action="enviar.php" name="material" id="material" class="validar" method="post" enctype="multipart/form-data">
				<div class="field">
					<label class="left">Material</label>
					<input type="text" name="material" id="material" class="validate[required]" />
				</div>			

				<div class="field">
					<label class="left">Costo</label>
					<input type="text" name="costo" id="costo" class="validate[required]" />
				</div>
				<div class="field">
					<input type="text" name="folio" id="folio" value="<?php echo $folio; ?>" readonly="true" class="small validate[required] text-input" style="display:none;" />
					<input type="text" name="hacer" id="hacer" value="material" readonly="true" class="small validate[required] text-input" style="display:none;" />
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
