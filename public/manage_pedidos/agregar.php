<?php 	
	session_start();
	include(__DIR__ . "/../../funciones.php");

	registrado();

	//Conexion de BD
	$con = conecta();	
?>

<!DOCTYPE HTML>
<html lang="es">
<head>
	<?php include(__DIR__ . "/../../modules/header.php"); ?>	

	<title>Nueva Orden</title>

	<link rel="stylesheet" href="<?php echo _BASE_URL; ?>/assets/js/select2/select2.css">
	<script src="<?php echo _BASE_URL; ?>/assets/js/select2/select2.js"></script>
	<script src="<?php echo _BASE_URL; ?>/assets/js/chained.js"></script>
	
	<script type="text/javascript">
		$(window).load(function(){
			$("div #uniform-empresa.selector").hide();
			$("div #uniform-planeador.selector").hide();
			$("div #uniform-responsable.selector").hide();
		});

		$(document).ready(function(){

			$("#municipio").chained("#estado");
			$("#estado").on('change', function(event) {
 				$.uniform.update();
		    });		

			$("input[name='cliente']").click(function(){
				var radio      = $("input[name='cliente']:checked").val();
				var registrado = $("#solicitante1,#departamento1,#fecha_deseada1");
				var nuevo      = $("#solicitante2,#departamento2,#fecha_deseada2");

				if ( radio == 0 ){
					$("#datos-cliente").show('slow');
					$("#cliente-registrado").hide('slow');					
					$("#direccion-envio").show('slow');
					registrado.attr('disabled',true);
					nuevo.removeAttr('disabled');
				}else if ( radio == 1 ){
					$("#cliente-registrado").show('slow');
					$("#datos-cliente").hide('slow');
					$("#direccion-envio").hide('slow');
					registrado.removeAttr('disabled');
					nuevo.attr('disabled',true);
				}
			});

			$(".buscador").select2();
			
		});
	</script>
</head>
<body>

<!-- Start: Page Wrap -->
<div id="wrap" class="container_24">

	<!-- Menu: Start -->	
	<?php include(__DIR__ . "/../../modules/menu.php"); ?>
	<!-- Menu: End -->
	<form action="enviar.php" method="post" class="validar" enctype="multipart/form-data" accept-charset="UTF-8">
        <div class="row">
            <div class="col-lg-12">

                <div class="box_top">
                    <h2 class="icon frames">Cliente</h2>
                </div>

                <div class="box_content padding">
                    <div class="form-group">
                        <label class="left">Tipo de cliente*</label>
                        <label class="radio-inline"><input type="radio" name="cliente" id="cliente0" value="0" class="validate[required]" />Nuevo</label>
                        <label class="radio-inline"><input type="radio" name="cliente" id="cliente1" value="1" class="validate[required]" />Registrado</label>
                    </div>
                    <div id="cliente-registrado" style="display:none;" >
                        <div class="form-group">
                            <label class="left">Empresa*</label>
                            <select name="empresa_registrada" id="empresa" class="validate[required] buscador" style="width:200px;">
                                <option value="">Seleccione</option>
                                <?php
                                    $b = $con->prepare("SELECT id_cliente,cliente FROM aio_cliente");
                                    $b->execute();
                                ?>
                                <?php while ( $r = $b->fetchObject() ): ?>
                                    <option value="<?php echo $r->id_cliente; ?>"><?php echo $r->cliente; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="left">Solicitante*</label>
                            <input type="text" name="solicitante" id="solicitante1" class="form-control validate[required]" placeholder="Nombre Apellido"  />
                        </div>
                        <div class="form-group">
                            <label class="left">Departamento*</label>
                            <input type="text" name="departamento" id="departamento1" class="form-control validate[required]" placeholder="Departamento"  />
                        </div>
                        <div class="form-group">
                            <label class="left">Fecha Deseada*</label>
                            <input type="text" name="fecha_deseada" id="fecha_deseada1" class="form-control validate[required] date datepicker" autocomplete="off" />
                        </div>
                    </div>
                </div>
            </div>

            <div id="datos-cliente" style="display:none;">
                <div class="col-lg-12">
                    <div class="box_top">
                        <h2 class="icon frames">Informacion Cliente</h2>
                    </div>

                    <div class="box_content padding">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="left">Solicitante*</label>
                                    <input type="text" name="solicitante" id="solicitante2" class="form-control validate[required]" placeholder="Nombre Apellido"  />
                                </div>
                                <div class="form-group">
                                    <label class="left">Departamento*</label>
                                    <input type="text" name="departamento" id="departamento2" class="form-control validate[required]" placeholder="Departamento"  />
                                </div>

                                <div class="form-group">
                                    <label class="left">Empresa*</label>
                                    <input type="text" name="empresa" id="empresa" class="form-control validate[required]" placeholder="Empresa" />
                                </div>
                                <div class="form-group">
                                    <label class="left">Correo</label>
                                    <input type="text" name="email" id="email" class="form-control validate[required,custom[email]]" placeholder="correo@dominio.com" />
                                </div>

                                <div class="form-group">
                                    <label class="left">Telefono</label>
                                    <input type="text" name="tel" id="tel" class="form-control validate[groupRequired[tel],custom[phone]]" placeholder="XXX-XX-XX" />
                                </div>
                                <div class="form-group">
                                    <label class="left">Celular</label>
                                    <input type="text" name="cel" id="cel" class=" form-control validate[groupRequired[tel],custom[phone]]" placeholder="044-614-XXX-XX-XX" />
                                </div>
                                <div class="form-group">
                                    <label class="left">Fecha Deseada*</label>
                                    <input type="text" name="fecha_deseada" id="fecha_deseada2" class="form-control validate[required,custom[date]] date" autocomplete="off" />
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="left">Razon Social*</label>
                                    <input type="text" name="razonsocial" id="razonsocial" class="big form-control validate[required]" />
                                </div>

                                <div class="form-group">
                                    <label class="left">RFC*</label>
                                    <input type="text" name="rfc" id="rfc" class="big form-control validate[required]" />
                                </div>

                                <div class="form-group">
                                    <label class="left">Calle*</label>
                                    <input type="text" name="calle" id="calle" class="form-control validate[required]" placeholder="Calle" />
                                </div>
                                <div class="row form-group">
                                    <label class="col-xs-12">Numero*</label>
                                    <div class="col-xs-8">
                                        <input type="text" name="noext" id="noext" class="peque form-control validate[required]" placeholder="#" />
                                    </div>
                                    <div class="col-xs-4">
                                        <input type="text" name="int" id="int" class="peque form-control" placeholder="Int" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="left">Colonia*</label>
                                    <input type="text" name="colonia" id="colonia" class="form-control validate[required]" placeholder="Colonia" />
                                </div>

                                <div class="form-group">
                                    <label class="left">Codigo Postal</label>
                                    <input type="text" name="cp" id="cp" class="form-control small" placeholder="#" />
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-12">Estado y municipio*</label>
                                    <div class="col-lg-6">
                                        <select name="estado" id="estado" class="form-control validate[required]">
                                            <option value="" selected="true">Seleccione</option>
                                            <option value="8">Chihuahua</option>
                                            <?php
                                                $b = $con->prepare("SELECT id,nombre FROM aio_estados WHERE nombre!='Chihuahua'");
                                                $b->execute();
                                                $b->bindColumn('id',$clave);
                                                $b->bindColumn('nombre',$estado);
                                                while ( $r = $b->fetch() ){
                                                    echo '<option value="'.$clave.'">'.$estado.'</option>';
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-lg-6">
                                        <select name="municipio" id="municipio" class="form-control validate[required]">
                                            <option value="" selected="true">Seleccione</option>
                                            <?php
                                                $b = $con->prepare("SELECT clave,nombre,estado_id FROM aio_municipios");
                                                $b->execute();
                                                $b->bindColumn('clave',$clave);
                                                $b->bindColumn('nombre',$municipio);
                                                $b->bindColumn('estado_id',$estado_id);

                                                while ( $r = $b->fetch() ){
                                                    echo '<option value="'.$clave.'" class="'.$estado_id.'">'.$municipio.'</option>';
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form aticulos-->
            <div class="col-lg-6">
                <div class="box_top">
                    <h2 class="icon frames">Personal</h2>
                </div>
                <div class="box_content padding">
                    <div class="form-group">
                        <label calss="letf" style="display: inline;">Planeador*</label>
                        <select name="planeador" id="planeador" class="validate[required] buscador" style="width:200px;">
                            <option value="">Seleccione</option>
                            <?php
                                $b = $con->prepare("SELECT id_usuario,nombre,apellido FROM aio_personal WHERE (nivel='1' OR nivel='2') AND id!=1");
                                $b->execute();
                            ?>
                            <?php while ( $r = $b->fetchObject() ): ?>
                                <option value="<?php echo $r->id_usuario; ?>"><?php echo $r->nombre.' '.$r->apellido; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label calss="letf" style="display: inline;">Responsable*</label>
                        <select name="responsable" id="responsable" class="validate[required] buscador" style="width:200px;">
                            <option value="">Seleccione</option>
                            <?php
                                $b = $con->prepare("SELECT id_usuario,nombre,apellido FROM aio_personal WHERE nivel='1' AND id!='1'");
                                $b->execute();
                            ?>
                            <?php while ( $r = $b->fetchObject() ): ?>
                                <option value="<?php echo $r->id_usuario; ?>"><?php echo $r->nombre.' '.$r->apellido; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    </div>
                </div>
            <!-- Form arciculos -->

            <div class="col-lg-6" >
                <div class="box_top">
                    <h2 class="icon frames">Trabajo Solicitado</h2>
                </div>
                <div class="box_content">
                    <textarea name="trabajo_solicitado" id="trabajo_solicitado" class="validate[required] editor_simple" style="height: 220px; width: 412px; resize: none;"></textarea>
                </div>
            </div>

            <div class="col-lg-6" >
                <div class="box_top">
                    <h2 class="icon frames">Trabajo a Realizar</h2>
                </div>
                <div class="box_content">
                    <textarea name="trabajo_realizar" id="trabajo_realizar" class="validate[required] editor_simple" style="height: 220px; width: 412px; resize: none;"></textarea>
                </div>
            </div>

            <div class="col-lg-6" >
                <div class="box_top">
                    <h2 class="icon frames">Diagnostico de la falla y observaciones</h2>
                </div>
                <div class="box_content">
                    <textarea name="diagnostico_observaciones" id="diagnostico_observaciones" class="validate[required] editor_simple" style="height: 295px; width: 412px; resize: none;"></textarea>
                </div>
            </div>

            <div class="col-lg-12" >
                <div class="box_top">
                    <h2 class="icon frames">Informacion general</h2>
                </div>

                <div class="box_content padding">
                    <div class="form-group">
                        <label class="left">Sucursal*</label>
                        <select name="sucursal" id="sucursal" class="form-control validate[required]">
                            <option value="">Seleccione</option>
                            <?php
                                $sucursal_usuario = sucursal($_SESSION['id']);
                                $b = $con->prepare("SELECT nombre,id_sucursal FROM aio_sucursal");
                                $b->execute();
                                while( $r = $b->fetchObject() ):
                            ?>
                                <option value="<?php echo $r->id_sucursal; ?>" <?php if ($r->id_sucursal == $sucursal_usuario->id_sucursal) echo 'selected="selected";' ?> ><?php echo $r->nombre; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="left">Prioridad*</label>
                        <label class="radio-inline"><input type="radio" name="prioridad" id="prioridad1" value="1" class="validate[required]" /> 1</label>
                        <label class="radio-inline"><input type="radio" name="prioridad" id="prioridad2" value="2" class="validate[required]" /> 2</label>
                        <label class="radio-inline"><input type="radio" name="prioridad" id="prioridad3" value="3" class="validate[required]" /> 3</label>
                    </div>

                    <div class="form-group">
                        <label class="left">Turno*</label>
                        <label class="radio-inline"><input type="radio" name="turno" id="turno1" value="1" class="validate[required]" /> 1</label>
                        <label class="radio-inline"><input type="radio" name="turno" id="turno2" value="2" class="validate[required]" /> 2</label>
                        <label class="radio-inline"><input type="radio" name="turno" id="turno3" value="3" class="validate[required]" /> 3</label>
                    </div>

                    <div class="form-group">
                        <input type="hidden" name="hacer" id="hacer" value="insertar" style="display:none;"/>
                        <button class="btn btn-success">Enviar e imprimir orden</button>
                    </div>
                </div>
            </div>
        </div>
	</form>

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
