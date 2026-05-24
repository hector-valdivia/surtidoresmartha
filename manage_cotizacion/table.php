<?php 	
	session_start();
	include('../funciones.php');
	
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
	<?php include('../modules/header.php'); ?>	
	<title>Administrar Trabajos</title>
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
			<h2 class="icon pages">Tabla de Cotizaciones</h2>
			<ul class="sorting">
				<li><a href="agregar.php" class="btn">+ Agregar Cotización</a></li>
			</ul>
		</div>
		<!-- Box Header: End -->
		
		<!-- Box Content: Start -->
		<div class="box_content">		
			<!-- Pedidos Pendientes: Start -->
			<style type="text/css">
				.pedido th, td, caption { padding: 10px 10px; }
			</style>
			<table id="tabla_cotizacion" class="pedido">
				<thead>
					<tr>
						<th class="align_left" style="width:15%;">ID</th>
						<th class="align_left">Para</th>
						<th class="align_left" style="width:20%;">Asunto</th>
						<th class="align_left" style="width:20%;">Fecha</th>
						<th class="align_left">Creada por</th>
						<th class="align_left center">Herramientas</th>
					</tr>
				</thead>
				<tbody class="content sorting"></tbody>
			</table>
			<div class="table_actions">
				<a href="agregar.php" class="btn btn-info">Nueva Cotizacion</a>
			</div>
		<!-- Pedidos pendientes: End -->		
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
<script type="text/javascript" src="<?php echo _BASE_URL; ?>/assets/js/gritter/jquery.gritter.js"></script>
<script src="<?php echo _BASE_URL; ?>/assets/js/customrequiest.js"></script>
<script src="<?php echo _BASE_URL; ?>/assets/js/jquery.confirm.min.js"></script>

<script type="text/javascript">
	$(document).ready(function($) {
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
		
		////////////////////////////////////////////////////////////////////	
		//Datatables
		$('#tabla_cotizacion').dataTable({
			"processing": true,
			"serverSide": true,
			"ajax": "ajax_cotizaciones.php"
		});

		////////////////////////////////////////////////////////////////////	
		//Borrar cotizacion
		$(document).on('click', '#tabla_cotizacion tbody .borrar', function(event) {
			event.preventDefault();
			var id = $(this).data('id');
			var row = $(this).closest('tr');

			$.confirm({
				text: "Esta seguro que quiere eliminar la cotización",
				title: "Confirmar",
				confirm: function(button) {
					//Enviar por ajax
					$.customRequest(
						'hacer',
						{hacer:'borrar',id_cotizacion:id},
						{
							onSuccess: function(result) {
								row.remove();
							}
						}
					);
				},
				cancel: function(button) {},
				confirmButton: "Borrar",
				cancelButton: "Cancelar",
				post: true
			});
		});

		////////////////////////////////////////////////////////////////////	
		//Al cerrar el modal
		$('#moda_borrar').on('hidden.bs.modal', function (e) {
			$('#form_borrar #id').val('');
		});
	});
</script>

</body>

</html>