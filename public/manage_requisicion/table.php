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
			<h2 class="icon pages">Tabla de Requisiciones</h2>
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
			<table id="tabla_requisicion" class="pedido">
				<thead>
					<tr>
						<th class="align_left" width="5%">ID</th>
						<th class="align_left" width="30%">Descripcion</th>
						<th class="align_left" width="8%">Fecha</th>
						<th class="align_left" width="10%">Sucursal</th>
						<th class="align_left" width="10%">Solicita</th>
						<th class="align_left" width="15%">Autorizo</th>
						<th class="aligh_left" width="5%">Status</th>
						<th class="align_left center">Herramientas</th>
					</tr>
				</thead>
				<tbody class="content sorting">
				</tbody>
			</table>
			<div class="table_actions">
				<a href="agregar.php" class="btn btn-info">Nueva Requisicion</a>
			</div>
		<!-- Pedidos pendientes: End -->		
		</div>
	</div>

	<!-- Footer Grid: Start -->
	<?php include(__DIR__ . "/../../modules/pie.php"); ?>
	<!-- Footer Grid: End -->

</div>
<!-- End: Page Wrap -->

<!-- funciones de jquery: start -->
<?php include(__DIR__ . "/../../modules/js.php"); ?> 
<!-- funciones de jquery: end -->
<script type="text/javascript" src="<?php echo _BASE_URL; ?>/assets/js/gritter/jquery.gritter.js"></script>
<script src="<?php echo _BASE_URL; ?>/assets/js/customrequiest.js"></script>
<script src="<?php echo _BASE_URL; ?>/assets/js/jquery.confirm.min.js"></script>

<script type="text/javascript">
	$(document).ready(function($) {
		/*===========================================================*/
		/*	Datatable
		/*===========================================================*/
		var $tabla = $('#tabla_requisicion').DataTable({
			"processing": true,
			"serverSide": true,
			"info": false,
			"ajax":{ 
				"url": "datatable.requisicion.php",
				"type": "GET"
			}
		});


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

		///////////////////////////////////////////////////////////////////
		//Generar el pdf
		$(document).on('click','.pdf',function(){
			//Id obtenido del boton
			var id = { 'id_requisicion': $(this).data('id') }
			//Boton hacer
			var form = { 'hacer':'imprimir_pdf' };
			//Se extiendo el obejto pedido en form, y se concatenan en una sola variable
			$.extend(form, id);
			//Enviar por ajax
			$.customRequest(
				'hacer',
				form,
				{
					onSuccess: function(result) {
						mensaje(result);
						if ( result.hacer == 'imprimir_pdf' ){
							window.open(result.archivo);
						}
					}
				}
			);
		});

		///////////////////////////////////////////////////////////////////
		//Borrar cotizacion
		$('#tabla_requisicion tbody').on('click', '.borrar', function(event) {
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
						{hacer:'borrar',id_requisicion:id},
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