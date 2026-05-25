<?php
	session_start();
	include(__DIR__ . "/../../funciones.php");

	registrado();
	nivel( $comparado=1 );
	
	//Conexion de BD
	$con = conecta();
?>
<!DOCTYPE HTML>
<html lang="es">
<head>
	<title>Administrar Secciones</title>
	<?php include(__DIR__ . "/../../modules/header.php"); ?>
	<style>
		.pedido th, td, caption { padding: 10px 10px; }
	</style>
</head>

<body>

<!-- Start: Page Wrap -->
<div id="wrap" class="container_24">
	
<?php include(__DIR__ . "/../../modules/menu.php"); ?>
		
<?php mostrar_errores(); ?>

<div id="response" class="grid_24"></div>
<!-- 100% Box Grid Container: Start -->
<div class="grid_24">
	<!-- Box Header: Start -->
	<div class="box_top">
		<?php 
			$c_menus = $con->prepare("SELECT COUNT(id) FROM aio_orden");
			$c_menus->execute();
			$numero = $c_menus->fetchColumn();
		?>
		<h2 class="icon pages">Ordenes de trabajo<span class="tip" title="Cantidad de pedidos"><?php echo $numero; ?></span></h2>
				
		<!-- Tab Select: Start -->
		<ul class="ordenes_trabajo">
			<li><a href="#" data-tipo="abierto" class="active filtro_tipo">En proceso</a></li>
			<li><a href="#" data-tipo="vencido" class="filtro_tipo">Vencidos</a></li>
			<li><a href="#" data-tipo="cancelado" class="filtro_tipo">Cancelados</a></li>
			<li><a href="#" data-tipo="terminado" class="filtro_tipo">Terminado</a></li>
			<li><a href="#" data-tipo="todos" class="filtro_tipo">Todos</a></li>
		</ul>
		<!-- Tab Select: End -->		
	</div>
	<!-- Box Header: End -->
	
	<!-- Box Content: Start -->
	<div class="box_content">
		
		<!-- News Table Tabs: Start -->
		<div class="tabs">
		
			<!-- Pedidos Pendientes: Start -->
			<div id="pedidos_pendientes">
				<style type="text/css">

				</style>
				<table id="ordenes" class="pedido">
					<thead>
						<tr>
							<th class="align_left" style="width: 10%;">Folio</th>
							<th class="align_left" style="width: 25%;">Cliente</th>
							<th class="align_left">Sucursal</th>
							<th class="align_left">Fecha Orden</th>
							<th class="align_left">Inicio Servicio</th>
							<th class="align_left">Fecha deseada</th>
							<th class="align_left center">Herramientas</th>
						</tr>
					</thead>
					<tbody class="content sorting"></tbody>	
				</table>				
				<div class="table_actions">
					<a href="agregar.php" class="btn btn-info">Nueva Orden de Trabajo</a>
				</div>
			</div>
			<!-- Pedidos pendientes: End -->

		</div>
	</div>
	<!-- Box Content: End -->
	
</div>
<!-- 100% Box Grid Container: End -->

<!-- Footer Grid: Start -->
<?php include(__DIR__ . "/../../modules/pie.php"); ?>
<!-- Footer Grid: End -->

</div>
<!-- End: Page Wrap -->

<?php 
	include(__DIR__ . "/../../modules/js.php");
?>
<script type="text/javascript">
	$(document).ready(function($) {
		let $tipo = 'abierto';

		/*===========================================================*/
		/*	Datatable
		/*===========================================================*/
		const $tabla = $('#ordenes').DataTable({
			"processing": true,
			"serverSide": true,
			"info": false,
			"ajax":{ 
				"url": "ajax_ordenes.php",
				"type": "POST",
				"data": function(d){
					d.tipo = $tipo;
				}
			}
		});

		/*=========== Filtrar por tipo ==============================*/
		$('.filtro_tipo').click(function(e) {
			e.preventDefault();
			$tipo = $(this).data('tipo');
			$tabla.ajax.reload();
		});
	});
</script>
</body>
</html>