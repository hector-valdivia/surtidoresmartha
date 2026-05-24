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
	<title>Inventario</title>	
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
				$c_noticias = $con->prepare("SELECT COUNT(id) FROM aio_producto");
				$c_noticias->execute();
				$numero = $c_noticias->fetchColumn() 
			?>
			<h2 class="icon pages">Inventario<span class="tip" title="Cantidad de productos"><?php echo $numero; ?></span></h2>		
		</div>
		<!-- Box Header: End -->
	
		<!-- Box Content: Start -->
		<div class="box_content">
		
			<!-- News Table Tabs: Start -->
			<div class="tabs">
		
				<!-- News Sorting Table: Start -->
				<div id="productos">
					<style type="text/css">
						.inventario th, caption {
							padding: 10px 10px;
							font-size: 10pt;
						}

						.inventario td{
							padding: 8px;
							font-size: 10pt;
						}
					</style>				
					<table class="sorting inventario">
						<thead>
							<tr>
								<th class="align_left">Titulo</th>
								<th class="align_left">Disponibles</th>
								<th class="align_left">Rentados</th>
								<th class="align_left">Sin rentar</th>
	                            <th class="align_left center">Tools</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$b_noticias = $con->prepare("SELECT * FROM aio_producto");
								$b_noticias->execute();								 
								while( $r = $b_noticias->fetchObject() ):

									$b_r = $con->prepare("SELECT * FROM 
										aio_pedidos_productos INNER JOIN aio_pedidos 
										ON aio_pedidos_productos.folio = aio_pedidos.folio 
										WHERE producto=:producto AND (estado_pedido='abierto' OR estado_pedido='vencido') ");
									$b_r->bindParam(':producto',$r->producto);
									$b_r->execute();

									$cantidad = 0;

									$mp = '
										<div id="ver'.$r->id.'" style="display:none;">
											<table>
												<thead>
													<tr>
														<th>Folio</th>
														<th>Cliente</th>
														<th>Cantidad</th>
														<th>Fecha Fin</th>
													<tr>
												</thead>
												<tbody>';

									while ( $r_r = $b_r->fetchObject() ){ 
										$cantidad+= $r_r->cantidad;

										$mp.= '											
											<tr>
												<td><a href="'._BASE_URL.'/manage_pedidos/info?id='.encriptar($r_r->folio).'">'.$r_r->folio.'</a></td>
												<td>'.nombre_cliente($r_r->id_cliente).'</td>
												<td>'.$r_r->cantidad.'</td>
												<td>'.substr($r_r->hasta, 0,10).'</td>
											</tr>';
									}

									$mp.= '</tbody></table></div>';

									if ( $r->inventario == 0) {
										$total 		 = 'infi';
										$sin_prestar = 'infi';
									}else{
										$total 		 = $r->inventario;
										$sin_prestar = $r->inventario-$cantidad;
									}

									if ( $sin_prestar != 'infi' && 0>$sin_prestar ) $sin_prestar = '<a href="#antencion_'.$r->id.'" class="popup">Atencion</a><div id="antencion_'.$r->id.'" style="display:none; width:150px;"><h2>Ahi una mala configuracion en el inventario debido a que se presenta<br/>este producto <b><i>'.$r->producto.'</i></b> con numeros negativos</h2></div>';
							?>
							<tr>
								<td class="align_left"><?php echo $r->producto; ?></td>
								<td><?php echo $total; ?></td>								
								<td><?php echo $cantidad; ?></td>
								<td><?php echo $sin_prestar; ?></td>
								<td class="align_left tools center">																		
									<a href="#ver<?php echo $r->id; ?>" class="popup view tip" title="ver">Ver</a>
									<a href="#editar_producto<?php echo $r->id; ?>" class="popup edit tip" title="editar">Editar</a>
								</td>
							</tr>
							<?php								
									$editar[]='
										<div id="editar_producto'.$r->id.'" style="display:none; width:460px; height:700px; overflow:hidden;">
											<p style="width:420px; text-align:justify;">
												¿Seguro quiere editar el producto <b>'.$r->producto.'</b>? Por favor tenga en cuenta que la edicion de cantidad en inventario puede resultar en negativos, si es asi se tomara el valor minimo +1 y se actualizara la informacion
											</p>
											<form action="enviar.php" name="editar" id="editar" class="validar" method="post">
												
												<label class="left">Inventario:</label>
												<input type="text" name="inventario" id="inventario" value="'.$r->inventario.'" class="validate[required],custom[onlyNumberSp]">

												<input type="text" name="id" id="id" value="'.$r->id.'" readonly="readonly" style="display:none;" />
												<input type="text" name="producto" id="producto" value="'.$r->producto.'" readonly="readonly" style="display:none;" />
												<input type="text" name="hacer" id="hacer_producto" value="inventario" readonly="readonly" style="display:none;" />

												<div style="float:right">
													<button>Enviar</button>
													<button class="nyroModalClose">Cerrar</button>
												</div>
												<br/><br/>
											</form>																					
										</div>';

										if ( $cantidad==0 ){
											$mp = '
												<div id="ver'.$r->id.'" style="display:none;">
													<p style="width:420px; text-align:center; font-size:14pt; color:black;">Al momento no ahi ningun prestamo de este equipo</p>
												</div>';
										}

										$modal_producto[] = $mp;

								endwhile; 
							?>
							
						</tbody>
					</table>
					<?php if (!empty($editar)) foreach ($editar as $modal) echo $modal; ?>
					<?php if (!empty($modal_producto)) foreach ($modal_producto as $modal) echo $modal; ?>
				</div>
				<!-- News Sorting Table: End -->
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