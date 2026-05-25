<?php 	
	session_start();
	include(__DIR__ . "/../../funciones.php");
	
	//Comprobar Inicio de Sesion
	registrado();

	//Conexion de BD
	$con = conecta();

	//Id del usuario logueado
	$id = desencriptar( limpiar($_SESSION['id']) );

	//Informacion de la sucursal del usuario actual
	$sucursal = sucursal();	
?>

<!DOCTYPE HTML>
<html lang="es">
<head>

	<?php include(__DIR__ . "/../../modules/header.php"); ?>	
	<title>Administrar Trabajos</title>
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
	<?php include(__DIR__ . "/../../modules/menu_taller.php"); ?>
	<!-- Menu: End -->
		
	<?php mostrar_errores(); ?>

	<!-- 100% Box Grid Container: Start -->
	<div class="grid_24">

		<!-- Box Header: Start -->
		<div class="box_top">
			<?php				
				$c = $con->prepare("SELECT COUNT(id) FROM aio_orden WHERE sucursal=:sucursal AND estado_orden='abierto' AND planeador=:planeador ORDER BY fecha_orden ASC");
				$c->bindParam(':sucursal',  $sucursal->id_sucursal);
				$c->bindParam(':planeador',$id);			
				$numero = $c->fetchColumn();
			?>
			<h2 class="icon pages">Ordenes de Trabajo<span class="tip" title="Num. usuarios registrados"><?php echo $numero; ?></span></h2>	
		</div>
		<!-- Box Header: End -->
		
		<!-- Box Content: Start -->
		<div class="box_content">		
			<!-- Pedidos Pendientes: Start -->
			<style type="text/css">
				.pedido th, td, caption {
					padding: 10px 10px;
				}
			</style>
			<table class="sorting pedido">
				<thead>
					<tr>
						<th class="align_left" style="width:105px;">Folio</th>
						<th class="align_left">Cliente</th>
						<th class="align_left" style="width:95px;">Fecha Orden</th>
						<th class="align_left" style="width:95px;">Inicio Servicio</th>
						<th class="align_left" style="width:95px;">Fecha deseada</th>
						<th class="align_left center">Herramientas</th>
					</tr>
				</thead>
				<tbody class="content">
					<?php
						$b = $con->prepare("SELECT * FROM aio_orden WHERE sucursal=:sucursal AND estado_orden='abierto' AND planeador=:planeador ORDER BY fecha_orden ASC");
						$b->bindParam(':sucursal',  $sucursal->id_sucursal);
						$b->bindParam(':planeador',$id);
						$b->execute();
					?>
					<?php while( $r = $b->fetchObject() ): ?>

						<tr id="arrayorder_<?php echo $r->id; ?>">
							<td class="align_left"><?php echo $r->folio; ?></td>
							<td><?php echo nombre_cliente($r->id_cliente); ?></td>
							<td><?php echo substr($r->fecha_orden, 0,10); ?></td>
							<td><?php echo substr($r->fecha_inicio, 0,10); ?></td>
							<td><?php echo substr($r->fecha_deseada, 0,10); ?></td>
							<td class="align_left center">
								<a href="editar?id=<?php echo encriptar($r->folio); ?>" class="edit tip" title="Editar">Editar</a>
							</td>
						</tr>
					<?php endwhile; ?>
				</tbody>
			</table>
		<!-- Pedidos pendientes: End -->		
		</div>
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