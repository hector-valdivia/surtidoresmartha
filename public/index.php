<?php 
	session_start();
	include(__DIR__ . "/../funciones.php");	
?>
<!DOCTYPE HTML>
<html lang="es">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Dashboard</title>
	<?php include(__DIR__ . "/../modules/header.php"); ?>
</head>
<body>

<!-- Start: Page Wrap -->
<div id="wrap" class="container_24">

	<!-- Header Grid Container: Start -->
	<div class="login">
	
   	<?php   	 
		if(isset($_SESSION['error'])){
			foreach($_SESSION['error'] as $error)
				echo '<div class="notice error"><p><b>Error:&nbsp;</b>'.$error.'.</p></div>';
				unset($_SESSION['error']);			
		}
	?>
		
		<!-- Header: Start -->
		<div id="header">
				
			<!-- Logo: Start -->
			<a href="#" id="logo">AI PANEL</a>
			<!-- Logo: End -->
			
			<!-- Login: Start -->
			<form action="/login.php" method="post" id="login" class="form-inline">
				<div class="form-group">
					<!-- Username Input: Start -->
					<label for="username" class="sr-only label_username">User</label>
					<input type="text" name="user" value="" id="user" class="form-control password tip-stay validate" title="Ingresa tu seudonimo" />
				</div>

				<div class="form-group">
					<!-- Password Input: Start -->
					<label for="password" class="sr-only label_password">Password</label>
					<input type="password" name="pass" value="" id="pass" class="form-control password tip-stay validate" title="Ingresa tu Contraseña" />
				</div>
				
				<!-- Login Button: Start -->
				<input type="submit" value="login" class="btn btn-primary tip" title="Iniciar" />
				<!-- Login Button: End -->
			</form>
			<!-- Login: End -->
		</div>
		<!-- Header: End -->
		
		<!-- Breadcrumb Bar: Start -->
		<div id="breadcrumb">
			
			<!-- Breadcrumb: Start -->
			<ul class="left">
				<li class="icon key"><a href="#">Perdiste tu contraseña?</a></li>
			</ul>
			<!-- Breadcrumb: End -->
			
			<!-- Breadcrumb Icon Links: Start -->
			<ul class="right">
				<li><a href="#" class="icon home tip" title="Volver al Sitio">Home</a></li>
			</ul>
			<!-- Breadcrumb Icon Links: End -->
			
		</div>
		<!-- Breadcrumb Bar: End -->
		
	</div>
	<!-- Header Grid Container: End -->
    <?php include(__DIR__ . "/../modules/pie.php"); ?>
</div>

</body>

</html>
