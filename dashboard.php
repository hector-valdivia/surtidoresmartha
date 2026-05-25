<?php 
	session_start();
	include('funciones.php');

	registrado();
	nivel( $comparado = 1 );
	$id = desencriptar( limpiar( $_SESSION['id'] ) );
	//Conexion de BD
	$con = conecta();

	if($_POST){
		$keys_post = array_keys($_POST);
		foreach ($keys_post as $key_post){
			$$key_post = addslashes($_POST[$key_post]);
			error_log("variable $key_post viene desde $ _POST");
		}
	}
?>
<!DOCTYPE HTML>
<html lang="es">
<head>
	<title>AIO DASH</title>
	<?php include('modules/header.php'); ?>
</head>
<body>

<!-- Start: Page Wrap -->
<div id="wrap" class="container_24">
	
<?php include('modules/menu.php'); ?>

<!-- 100% Box Grid Container: Start -->
<div class="grid_24">
	
	<!-- Box Header: Start -->
	<div class="box_top">
		
		<h2 class="icon time">Ligas Rapidas</h2>
		
	</div>
	<!-- Box Header: End -->
	
	<!-- Box Content: Start -->
	<div class="box_content">
		
		<p class="center">
			<!-- List of big icons for quicklinks -->
			<a href="<?php echo _BASE_URL; ?>/manage_pedidos/table.php" class="big_button barcode"><span>+ Orden</span></a>
			<a href="<?php echo _BASE_URL; ?>/manage_clientes/table.php" class="big_button cliente"><span>+ Clientes</span></a>			
			<!--  <a href="<?php echo _BASE_URL; ?>/manage_trabajadores/table" class="big_button support"><span style="font-size:10px;">+Trabajadores</span></a> -->
			<a href="<?php echo _BASE_URL; ?>/manage_users/table.php" class="big_button add_user"><span>Personal</span></a>
			<a href="<?php echo _BASE_URL; ?>/manage_sucursales/table.php" class="big_button key"><span>Sucursales</span></a>
			<?php if ( $id == 96140 || $id == 86261 || $id == 12345 ): ?>
				<a href="<?php echo _BASE_URL; ?>/manage_cotizacion/table.php" class="big_button support"><span>Cotizaciones</span></a>
				<a href="<?php echo _BASE_URL; ?>/manage_reportes/table.php" class="big_button support"><span>Reportes</span></a>
			<?php endif; ?>
			<a href="<?php echo _BASE_URL; ?>/manage_requisicion/table.php" class="big_button support"><span>Requisiciones</span></a>
		</p>
		
	</div>
	<!-- Box Content: End -->
	
</div>
<!-- 100% Box Grid Container: End -->

<!-- Footer Grid: Start -->
<?php include('modules/pie.php'); ?>
<!-- Footer Grid: End -->

</div>
<!-- End: Page Wrap -->

<?php include('modules/js.php'); ?>

</body>

</html>
