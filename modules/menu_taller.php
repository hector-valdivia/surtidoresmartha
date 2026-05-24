	<!-- User Panel: Start -->
	<div id="userpanel">
			
		<!-- User: Start -->
		<ul id="user" class="dropdown">
			<li class="topnav">
				<!-- User Name -->
				<a href="#" class="top icon user"><?php echo apellido($_SESSION['id']); ?></a>
				<!-- User Dropdown Content: Start -->
				<ul class="subnav">
					<li><a href="<?php echo _BASE_URL; ?>/manage_users/table" class="icon settings">Conf. mi cuenta</a></li>   
					<li><a href="<?php echo _BASE_URL; ?>/logout" class="icon lock">Salir del Sistema</a></li>  
				</ul>  
				<!-- User Dropdown Content: End -->
			</li>
		</ul>
		<!-- User: End -->				
	</div>
		<!-- User Panel: End -->
		
		<!-- Header: Start -->
		<div id="header">
				
			<!-- Logo: Start -->
			<a href="<?php echo _BASE_URL; ?>/manage_ordenes/table.php" id="logo">Panel</a>
			<!-- Logo: End -->
			
			<!-- Navigation: Start -->
			<ul id="navigation" class="dropdown">
				<li><a class="dashboard active" href="<?php echo _BASE_URL; ?>/manage_ordenes/table.php">Dashboard</a></li>			
				
				
</div>