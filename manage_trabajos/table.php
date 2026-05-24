<?php
	session_start();
	include('../funciones.php');

	registrado();
	//Conexion de BD
	$con = conecta();
?>

<!DOCTYPE HTML>
<html lang="es">
<head>
	<?php include('../modules/header.php'); ?>	
	<title>Administrar Categorias</title>	
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
			<?php
				$c_noticias = $con->prepare("SELECT COUNT(id) FROM aio_trabajos");
				$c_noticias->execute();
				$numero = $c_noticias->fetchColumn() 
			?>
			<h2 class="icon pages">Categorias de trabajos<span class="tip" title="Cantidad de productos"><?php echo $numero; ?></span></h2>
		
			<!-- Tab Select: Start -->
			<ul class="sorting">
				<li><a href="#productos" class="active">Categorias</a></li>				
				<li><a href="#a_producto">Agregar categoria</a></li>
			</ul>
			<!-- Tab Select: End -->
		
		</div>
		<!-- Box Header: End -->
	
		<!-- Box Content: Start -->
		<div class="box_content">
		
			<!-- News Table Tabs: Start -->
			<div class="tabs">
		
				<!-- News Sorting Table: Start -->
				<div id="productos">
				
					<table class="sorting">
						<thead>
							<tr>
								<th class="align_left">Titulo</th>
	                            <th class="align_left center">Tools</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$b_noticias = $con->prepare("SELECT * FROM aio_trabajos");
								$b_noticias->execute();								 
								while( $r = $b_noticias->fetchObject() ):
							?>
							<tr>
								<td class="align_left"><?php echo $r->trabajos; ?></td>
								<?php if( $r->id != 1 || $r->id != 2 ): ?>
									<td class="align_left tools center">									
										<a href="#eliminar_<?php echo $r->id; ?>" class="popup delete tip" title="borrar">Borrar</a>
										<a href="#editar_<?php echo $r->id; ?>" class="popup edit tip" title="editar">Editar</a>
									</td>
								<?php endif ;?>
							</tr>
							<?php
								$modal[]='
									<div name="eliminar_'.$r->id.'" id="eliminar_'.$r->id.'" style="display:none;">										
										<h1>¿Seguro quiere eliminar la categoria <b>'.$r->trabajos.'</b>?</h1>																																							
										<form action="enviar.php" name="eliminar" id="eliminar_producto" class="validar" method="post">
											<input type="text" name="id" id="id" value="'.$r->id.'" readonly="readonly" style="display:none;" />
											<input type="text" name="hacer" id="hacer_producto_borrar" value="eliminar" readonly="readonly" style="display:none;" />											
											<center>			
												<button>Eliminar</button>
												<button class="nyroModalClose">Cerrar</button>
											</center>			
										</form>																		
									</div>

									<div name="editar_'.$r->id.'" id="editar_'.$r->id.'" style="display:none;">										
										<h1>¿Seguro quiere editar la categoria <b>'.$r->trabajos.'</b>?</h1>																																							
										<form action="enviar.php" name="eliminar" id="eliminar_producto" class="validar" method="post">
											<input type="text" name="categoria" id="categoria_editar" value="'.$r->trabajos.'" class="validate[required]" />
											<input type="text" name="id" id="id" value="'.$r->id.'" readonly="readonly" style="display:none;" />
											<input type="text" name="hacer" id="hacer_producto_borrar" value="editar" readonly="readonly" style="display:none;" />
											
											<br/><br/>
											
											<button>Guardar</button>
											<button class="nyroModalClose">Cerrar</button>
											
										</form>
										<br/><br/><br/>
									</div>';						
							
								endwhile; 
							?>
							
						</tbody>
					</table>
					<?php if (!empty($modal)) foreach ($modal as $modal) echo $modal;?> 
				</div>
				<!-- News Sorting Table: End -->				
	
				<!-- Crear Noticia: Start -->
				<div id="a_producto" class="padding">
					<form action="enviar.php" method="post" name="producto" id="producto" class="validar" enctype="multipart/form-data">	
						<p class="note">
							<span class="icon info"></span> 
							Debe rellenar todos los campos
						</p>               			
			    		<div class="field">
				  			<label class="left">Categoria:</label>
				  			<input type="text" name="categoria" id="categoria" class="big validate[required]">
						</div>
						
	           			<input type="text" name="hacer" id="hacer" value="insertar" readonly="true" class="validate[required]" style="display:none;"/>		
				  
				  		<button>Guardar</button>								
					</form>
				</div>
				<!-- Crear boletin: end -->																								
			</div>
			<!-- News Table Tabs: End -->
		</div>
		<!-- Box Content: End -->		
	</div>
	<!-- 100% Box Grid Container: End -->


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