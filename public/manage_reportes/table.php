<?php 	
	session_start();
	include(__DIR__ . "/../../funciones.php");
	
	//Comprobar Inicio de Sesion
	registrado();

	//Conexion de BD
	$con = conecta();
	$ext = conecta_extrangero();

	//Id del usuario logueado
	$id = desencriptar( limpiar($_SESSION['id']) );

	//Informacion de la sucursal del usuario actual
	$sucursal = sucursal();	
?>

<!DOCTYPE HTML>
<html lang="es">
<head>
	<?php include(__DIR__ . "/../../modules/header.php"); ?>	
	<title>Reportes</title>
	<style>
		.daterangepicker.dropdown-menu {
			top: 400px;
			left: 50% !important;
		}
	</style>
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
			<h2 class="icon pages">Reportes de usuarios</h2>
		</div>
		<!-- Box Header: End -->
		
		<!-- Box Content: Start -->
		<div class="box_content padding">
			<form name="reporte" id="reporte">
				<div class="form-group">
					<label>Empleado</label>
					<select name="empleado" id="empleado" class="form-control">
						<option value="todos">Todos</option>
						<?php 
							$b = $ext->prepare('SELECT DISTINCT id_personal, nombre FROM aio_asistencia');
							$b->execute();							
						?>
						<?php while ( $r = $b->fetchObject() ): ?>
							<option value="<?php echo $r->id_personal; ?>"><?php echo $r->nombre; ?></option>
						<?php endwhile; ?>
					</select>
				</div>

				<div class="form-group">
					<label>Rango de fechas</label>
					<div class="col-sm-12">
						<div id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc;">
							<i class="glyphicon glyphicon-calendar fa fa-calendar" style="font-size:20px;"></i><span style="margin-left: 10px;"></span>
							<input type="hidden" name="desde" id="desde" class="validate[required]">
							<input type="hidden" name="hasta" id="hasta" class="validate[required]">
						</div>
					</div>
				</div>

				<div class="form-group">
					<label>Sucursal</label>
					<select name="sucursal" id="sucursal" class="form-control">
						<option value="todas">Todas</option>
						<?php 
							$b = $con->prepare("SELECT id_sucursal, nombre FROM aio_sucursal");
							$b->execute();
						?>
						<?php while ( $r = $b->fetchObject() ): ?>
							<option value="<?php echo $r->id_sucursal; ?>"><?php echo $r->nombre; ?></option>
						<?php endwhile; ?>
					</select>
				</div>
				<div class="form-group">
					<button type="submit" class="btn btn-lg btn-primary enviar">Generar PDF</button>
				</div>
			</form>
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

	<!-- ===================== Datetimepicker ======================== -->
	<script src="<?php echo _BASE_URL; ?>/assets/js/bootstrap.daterangepicker/moment-with-locales.js"></script>
	<script src="<?php echo _BASE_URL; ?>/assets/js/bootstrap.daterangepicker/daterangepicker.js"></script>	
	<link rel="stylesheet" href="<?php echo _BASE_URL; ?>/assets/js/bootstrap.daterangepicker/daterangepicker.css">

	<script>
		$(document).ready(function() {
			/*===========================================================*/
			/*	Idioma de moment.js
			/*===========================================================*/	
			//moment.lang('es');
			moment.locale('es');

			/*===========================================================*/
			/*	Dateragepicker
			/*===========================================================*/	

			/*============== Dateragepicker Configuracion ===============*/
			var optionSet1 = {
				startDate: moment().subtract('days', 29),
				endDate: moment(),
				dateLimit: { days: 60 },
				// showDropdowns: true,
				showWeekNumbers: true,
				timePicker: false,
				timePickerIncrement: 1,
				timePicker12Hour: true,
				ranges: {
					'Hoy': [moment(), moment()],
					'7 dias atras': [moment().subtract('days', 6), moment()],
					'Este mes': [moment().startOf('month'), moment().endOf('month')],
					'Mes Pasado': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
				},
				opens: 'left',
				buttonClasses: ['btn btn-default'],
				applyClass: 'btn-small btn-primary blanco',
				cancelClass: 'btn-small',
				format: 'YYYY-MM-DD',
				separator: ' a ',
				locale: {
					applyLabel: 'Enviar',
					cancelLabel: 'Limpiar',
					fromLabel: 'Desde',
					toLabel: 'Hasta',
					customRangeLabel: 'Personalizada',
					daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi','Sa'],
					monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
					firstDay: 1
				}
			};

			/*=========== Dateragepicker funcion callback ================*/
			let cb = function(start, end) {
				$('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
				$('#desde').val( start.format('YYYY-MM-DD') );
				$('#hasta').val( end.format('YYYY-MM-DD') );
			}

			/*=========== Limpiar inputs ===============================*/
			$('#reportrange').on('cancel', function(ev, picker) {
				$('#reportrange span').html('');
				$('#desde, #hasta').val('');
			});

			/*=========== Dateragepicker crear ==========================*/
			$('#reportrange').daterangepicker(optionSet1, cb);

			/*===========================================================*/
			/*	Formulario agregar validation
			/*===========================================================*/	
			$("#reporte").validationEngine("attach", {
				autoPositionUpdate : true,
				onValidationComplete: function(form, status){
					var desde = $('#desde').val();
					var hasta = $('#hasta').val();
					if ( status == true ){
						if ( desde != '' && hasta != '' ){
							//Serializar la informacion del form
							var datos_form = $("#reporte").serialize();

							//Enviar por ajax
							$.customRequest(
								'reporte',
								datos_form,
								{
									onSuccess: function(result) {
										window.open(result.archivo);
									}
								}
							);
						}else alert('No olvide seleccionar el rango de fechas');
					}//if ( status == true )

				}//onValidationComplete: function(form, status)
			});
		});
	</script>
</body>

</html>