<?php
	session_start();
	include(__DIR__ . "/../../funciones.php");

	registrado();
	//Conexion de BD
	$con = conecta();
		
	if($_GET){
	    $keys_post = array_keys($_GET);
	    foreach ($keys_post as $key_post){
	     	$$key_post = $_GET[$key_post];
	     	error_log("variable $key_post viene desde $ _POST");
	    }
	}
	
	//Limpiar id
	$id = limpiar($id);
	
	$b = $con->prepare("SELECT * FROM aio_producto WHERE id=:id");
	$b->bindParam(':id', $id);
	$b->execute();
	$r = $b->fetchObject();
	$precios = unserialize($r->precios);	
?>

<!DOCTYPE HTML>
<html lang="es">
<head>
	<?php include(__DIR__ . "/../../modules/header.php"); ?>	
	<title>Editar Producto</title>
	<script src="<?php echo _BASE_URL; ?>/assets/js/sheepItPlugin.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
		    var articulo = $('#precios').sheepIt({
		        separator: '',
		     	allowRemoveLast: true,
		        allowRemoveCurrent: true,
		        allowRemoveAll: false,
		        allowAdd: true,
		        allowAddN: false,
		        maxFormsCount: 20,
		        minFormsCount: 1,
		        iniFormsCount: <?php echo count($precios); ?>,
			    removeLastConfirmation: true,
			    removeCurrentConfirmation: true,
    			removeLastConfirmationMsg: '¿Seguro quiere borrar este articulo?',
    			removeCurrentConfirmationMsg: '¿Seguro quiere borrar este articulo?',
		        removeAllConfirmationMsg: '¿Seguro quiere borrar este articulo?',
		        data: [
					<?php foreach( $precios as $p ): ?>
						{'precio[#index#]': '<?php echo $p[precio]; ?>','desde[#index#]': '<?php echo $p[desde]; ?>', 'hasta[#index#]': '<?php echo $p[hasta]?>'},
					<?php endforeach; ?>
				]
		    });			
		});	
	</script>	
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
			<h2 class="icon pages">Editar Articulo <?php echo $r->producto; ?></h2>		
		</div>
		<!-- Box Header: End -->
	
		<!-- Box Content: Start -->
		<div class="box_content padding" >
			<form action="enviar.php" method="post" nombre="producto" id="producto" class="validar" enctype="multipart/form-data">	
				<p class="note">
					<span class="icon info"></span> 
					Debe rellenar todos los campos
				</p>               			
	    		<div class="field">
		  			<label class="left">Nombre Producto:</label>
		  			<label><?php echo $r->producto; ?></label>
				</div>

				<div class="field">
					<label class="left">Inventario:</label>
					<input type="text" name="inventario" id="inventario" value="<?php echo $r->inventario; ?>" class="validate[required],custom[onlyNumberSp]">
				</div>

	    		<div class="field">
		  			<label class="left">Costo:</label>
		  			<input type="text" name="costo" id="costo" value="<?php echo $r->costo; ?>" class="validate[required],custom[number]">
				</div>
				
	    		<div id="precios">
	    			
			  		<label>Renta:</label>				  		
			  		
					<div id="precios_noforms_template"></div>
					<div id="precios_template" class="field">								
			  			<div style="display: inline;">Precio <input type="text" name="precio[#index#]" id="precio#index#" class="peque validate[required,custom[number]]"></div>
			  			<div style="display: inline; margin:20px;">Desde x dias <input type="text" name="desde[#index#]" id="desde#index#" class="peque validate[required,custom[onlyNumberSp]]"></div>
			  			<div style="display: inline; margin:20px;">Hasta x dias <input type="text" name="hasta[#index#]" id="hasta#index#" class="peque validate[required,custom[onlyNumberSp]]"></div>
			  			<div style="display: inline; margin:20px;">
			  				<a id="precios_remove_current"><img style="cursor:pointer; cursor: hand;" src="<?php echo _BASE_URL; ?>/img/delet.png" class="delete" /></a>
			  			</div>
			  		</div>				  			
			  			
					<div id="precios_controls" class="field">
						<button id="precios_add">+ precio</button>
						<button id="precios_remove_last">- precio</button>
					</div>					  			
				</div>
					
           		<input type="text" name="id" id="id" value="<?php echo $id; ?>" readonly="true" class="validate[required]" style="display:none;"/>
           		<input type="text" name="hacer" id="hacer" value="editar" readonly="true" class="validate[required]" style="display:none;"/>		
		  
		  		<button>Guardar</button>								
			</form>			
		</div>
		<!-- Box Content: End -->		
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