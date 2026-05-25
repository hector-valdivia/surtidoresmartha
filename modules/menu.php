<!-- User Panel: Start -->
<div id="userpanel">
	<!-- User: Start -->
	<ul id="user" class="dropdown">
		<li class="topnav">
			<!-- User Name -->
			<a href="#" class="top icon user"><?php echo apellido($_SESSION['id']); ?></a>
            <ul class="subnav" style="display: none; ">
				<li><a href="/manage_users/table.php" class="icon settings">Conf. mi cuenta</a></li>
				<li><a href="/logout.php" class="icon lock">Salir del Sistema</a></li>
			</ul>
		</li>
	</ul>
	<!-- User: End -->
</div>
<!-- User Panel: End -->
		
<!-- Header: Start -->
<div id="header">
	<!-- Logo: Start -->
	<a href="/dashboard.php" id="logo">Surtidores Martha</a>
	<!-- Logo: End -->
	
	<!-- Navigation: Start -->
	<ul id="navigation" class="visible-sm dropdown">
		<li class="topnav">
			<a class="frames" href="#">Ordenes de trabajo</a>
			<ul class="subnav" style="display: none;">
				<li><a class="icon list" href="/manage_pedidos/table.php">Ordenes de trabajo</a></li>
				<li><a class="icon list" href="/manage_pedidos/agregar.php">Agregar ordenes</a></li>
				<li><a class="pages" href="/manage_clientes/table.php">Clientes</a></li>
				<li><a class="pages" href="/manage_users/table.php">Personal</a></li>
				<li><a class="pages" href="/manage_trabajos/table.php">Categorias</a></li>
				<li><a class="pages" href="/manage_cotizacion/table.php">Cotizaciones</a></li>
				<li><a class="pages" href="/manage_reportes/table.php">Reportes</a></li>
				<li><a class="pages" href="/manage_requisicion/table.php">Requisicion</a></li>
				<li><a class="pages" href="/manage_sucursales/table.php">Sucursales</a></li>
			</ul>
		</li>
	</ul>

	<ul id="navigation" class="visible-md dropdown">
		<li class="topnav">
			<a class="frames" href="#">Ordenes</a>
			<ul class="subnav" style="display: none; ">
				<li><a href="/manage_pedidos/table.php" class="icon list">Ordenes de trabajo</a></li>
				<li><a href="/manage_pedidos/agregar.php" class="icon list">Agregar ordenes de trabajo</a></li>
			</ul>
		</li>
		
		<li class="topnav">
			<a class="frames" href="#">Usuarios</a>
			<ul class="subnav" style="display: none; ">
				<li><a class="pages" href="/manage_clientes/table.php">Clientes</a></li>
				<li><a class="pages" href="/manage_users/table.php">Personal</a></li>
				<li><a class="pages" href="/manage_trabajos/table.php">Categorias</a></li>
			</ul>
		</li>

		<li class="topnav">
			<a href="#" class="frames">Admin</a>
			<ul class="subnav" style="display:none;">
				<li><a href="/manage_cotizacion/table.php" class="pages">Cotizaciones</a></li>
				<li><a href="/manage_reportes/index.php" class="pages">Reportes</a></li>
				<li><a href="/manage_requisicion/table.php" class="pages">Requisicion</a></li>
			</ul>
		</li>

		<li class="topnav">
			<a class="frames" href="#">Sucursales</a>
			<ul class="subnav" style="display: none; ">
				<li><a class="pages" href="/manage_sucursales/table.php">Tabla de Sucursales</a></li>
			</ul>
		</li>
	</ul>

	<ul id="navigation" class="visible-lg dropdown">
		<li><a class="dashboard active" href="/dashboard.php">Dashboard</a></li>

		<li class="topnav">
			<a class="frames" href="#">Ordenes de trabajo</a>
			<ul class="subnav" style="display: none; ">
				<li><a href="/manage_pedidos/table.php" class="icon list">Ordenes de trabajo</a></li>
				<li><a href="/manage_pedidos/agregar.php" class="icon list">Agregar ordenes de trabajo</a></li>
			</ul>
		</li>
		
		<li class="topnav">
			<a class="frames" href="#">Usuarios</a>
			<ul class="subnav" style="display: none; ">
				<li><a class="pages" href="/manage_clientes/table.php">Clientes</a></li>
				<li><a class="pages" href="/manage_users/table.php">Personal</a></li>
				<li><a class="pages" href="/manage_trabajos/table.php">Categorias</a></li>
			</ul>
		</li>

		<li class="topnav">
			<a href="#" class="frames">Administracion</a>
			<ul class="subnav" style="display:none;">
				<li><a href="/manage_cotizacion/table.php" class="pages">Cotizaciones</a></li>
				<li><a href="/manage_reportes/index.php" class="pages">Reportes</a></li>
				<li><a href="/manage_requisicion/table.php" class="pages">Requisicion</a></li>
			</ul>
		</li>

		<li class="topnav">
			<a class="frames" href="#">Sucursales</a>
			<ul class="subnav" style="display: none; ">
				<li><a class="pages" href="/manage_sucursales/table.php">Tabla de Sucursales</a></li>
			</ul>
		</li>
	</ul>
</div>
