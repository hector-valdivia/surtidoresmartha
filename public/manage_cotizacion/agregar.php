<?php 	
	session_start();
	include(__DIR__ . "/../../funciones.php");
	registrado();

	//Conexión de BD
	$con = conecta();
	$user_logueado = info_personal( desencriptar( limpiar($_SESSION['id']) ) );

    $id = '';
	if (isset($_GET['id'] )){
		$id = desencriptar($_GET['id']);
		$c = $con->prepare("SELECT COUNT(id) FROM aio_cotizacion WHERE id=:id");
		$c->bindParam(':id',$id);
		$c->execute();

		if ( $c->fetchColumn() == 0 ){
            header('Location:table.php');
        }

        $b = $con->prepare("SELECT * FROM aio_cotizacion WHERE id=:id");
        $b->bindParam(':id',$id);
        $b->execute();

        $cot = $b->fetchObject();
    }
?>

<!DOCTYPE HTML>
<html lang="es">
<head>
	<?php include(__DIR__ . "/../../modules/header.php"); ?>
	<title>Cotización <?php echo !empty($id) ? $cot->id_cotizacion : 'Nueva'; ?></title>
</head>
<body>

<!-- Start: Page Wrap -->
<div id="wrap">

	<!-- Menu: Start -->	
	<?php include(__DIR__ . "/../../modules/menu.php"); ?>
	
	<!-- ==================== Tabla Costos ==================== -->
	<div class="row">
		<div class="col-lg-12">
			<!-- Box Header: Start -->
			<div class="box_top">
				<h2 class="icon pages">Cotización <?php echo $cot->id_cotizacion ?? ''; ?></h2>
				<ul class="sorting">
					<li><a class="btn" data-toggle="modal" data-target="#modal_agregar">+ Agregar</a></li>
				</ul>
			</div>
			<!-- Box Header: End -->
			
			<!-- Box Content: Start -->
			<div class="box_content">
				<!-- Pedidos: Start -->
				<div id="pedido">
					<table id="tabla_cotizacion">
						<thead>
							<tr>
								<th class="center" width="40"></th>
								<th class="center" width="600">Descripción</th>
								<th class="center">Cantidad</th>
								<th class="center">Unidad</th>
								<th class="center">P/U</th>
								<th class="center">Costo</th>
							</tr>
						</thead>
						<tbody id="tabla_cotizacion_body" class="content">
							<?php if ( !empty($id) ): ?>
								<?php
									$b = $con->prepare("SELECT * FROM aio_cotizacion_conceptos WHERE id_cotizacion=:id_cotizacion");
									$b->bindParam(':id_cotizacion',$cot->id_cotizacion);
									$b->execute();
									while( $r = $b->fetchObject() ):
								?>
									<tr>
										<td class="center" style="width: 15%;"><div class="btn-group"><button type="button" class="borrar btn btn-default"><span class="glyphicon glyphicon-remove-sign"></span></button><button type="button" class="editar btn btn-default"><span class="glyphicon glyphicon-edit"></span></button></div></td>
										<td class="center"><?php echo $r->descripcion; ?></td>
										<td class="center"><?php echo $r->cantidad; ?></td>
										<td class="center"><?php echo $r->unidad; ?></td>
										<td class="center">$<?php echo dinero($r->pu); ?></td>
										<td class="center">$<?php echo dinero($r->costo); ?></td>
									</tr>
								<?php endwhile; ?>
							<?php else:  ?>
								<tr id="cero"><td colspan="6" class="center">No se ah agregado nada a la cotización</td></tr>
							<?php endif; ?>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="5" style="text-align:right;"><b>Total neto:</b></td>
								<td class="align_left center"><b id="div_total_neto">$<?php if ( !empty($id) ) echo dinero($cot->total); ?></b></td>								
							</tr>							
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>

	<!-- ==================== Modal Agregar Costos ==================== -->
	<div id="modal_agregar" class="modal fade" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="btn btn-default close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title" id="titleAgregar">Agregar a la cotización</h4>
				</div>
				<form id="modal_form_agregar" method="POST">
					<div class="modal-body form">
						<div class="form-group">
							<label>Descripción</label>
							<input type="text" name="descripcion" id="descripcion" class="form-control validate[required]">
						</div>
						<div class="form-group">							
							<div class="row">
								<div class="col-xs-6">
									<label>Cantidad</label>
									<input type="text" name="cantidad" id="cantidad" class="form-control validate[required,custom[number]]" placeholder="Cantidad">
								</div>
								<div class="col-xs-6">
									<label>Unidad</label>
									<input type="text" name="unidad" id="unidad" class="form-control validate[required]" placeholder="Unidad">
								</div>
							</div>
						</div>
						<div class="form-group">
							<label>Precio Unitario</label>
							<input type="text" name="precio_unitario" id="precio_unitario" placeholder="$" class="form-control validate[required,custom[number]]">
						</div>						
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default btn-flat" data-dismiss="modal">Cancelar</button>
						<input type="hidden" name="hacer" id="hacer" value="agregar">
						<button type="submit" id="enviar" class="btn btn-primary btn-flat">+ Agregar</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	
	<!-- ==================== Informacion de la cotizacion ==================== -->
	<form method="POST" id="enviar_cotizacion">
		<div class="row">
			<div class="col-lg-6">
				<div class="box_top">
					<h2 class="icon frames">Notas</h2>
				</div>	
				<div class="box_content">
					<textarea name="nota" id="nota" class="editor_simple" style="height: 220px; width: 412px; resize: none;">
						<?php if( !empty($id) ) echo base64_decode( $cot->nota ); ?>
					</textarea>
				</div>
			</div>

			<div class="col-lg-6">
				<div class="box_top">
					<h2 class="icon frames">Información de la cotización</h2>			
				</div>
				<div class="box_content padding">
					<div class="field">
						<div class="form-group">
							<label>Para</label>
							<input type="text" name="para" id="para" placeholder="Nombre Empresa/Cliente" class="form-control validate[required]" value="<?php if( !empty($id) ) echo $cot->para; ?>">
						</div>
						<div class="form-group">
							<label>Con atencion a</label>
							<input type="text" name="atencion" id="atencion" placeholder="Nombre del Cliente" class="form-control validate[required]" value="<?php if( !empty($id) ) echo $cot->atencion; ?>">
						</div>
						<div class="form-group">
							<label>Asunto</label>
							<input type="text" name="asunto_correo" id="asunto_correo" class="form-control validate[required]" placeholder="Asunto del correo" value="<?php if( !empty($id) ) echo $cot->asunto; ?>">
						</div>					
						<div class="form-group">
							<label>Mi correo</label>
							<input type="text" name="mi_email" id="mi_email" class="form-control validate[required,custom[email]]" placeholder="@" value="<?php if( !empty($id) ) echo $cot->email_envio; else echo $user_logueado->email; ?>">
						</div>
						<div class="form-group">
							<label>Correo Cliente</label>
							<input type="text" name="cliente_email" id="cliente_email" class="form-control validate[required]" placeholder="@" value="<?php if( !empty($id) ) echo $cot->email_cliente; ?>">
						</div>
					</div>
					<div id="controls_informacion" class="field">
						<?php if ( !empty($id) ): ?>
							<button type="submit" class="btn btn-lg btn-info enviar" id="guardar" data-hacer="editar">Guardar</button>
							<button type="submit" class="btn btn-lg btn-primary enviar" id="guardar_enviar" data-hacer="editar_enviar">Enviar PDF</button>
							<button type="submit" class="btn btn-lg btn-warning enviar" id="guardar_nuevo_enviar" data-hacer="pdf_guardar">PDF y Guardar</button>
						<?php else: ?>
							<button type="submit" class="btn btn-lg btn-info enviar" id="guardar" data-hacer="guardar">Guardar</button>
							<button type="submit" class="btn btn-lg btn-primary enviar" id="guardar_enviar" data-hacer="guardar_enviar">Enviar PDF</button>
						<?php endif; ?>
					</div>
					<input type="hidden" name="total_neto" id="total_neto" value="<?php if ( !empty($id) ) echo cleanNumber($cot->total); else echo 0; ?>">
					<input type="hidden" name="hacer" id="hacer" value="nada">
					<input type="hidden" name="id_cotizacion" id="id_cotizacion" value="<?php if ( !empty($id) ) echo $cot->id_cotizacion; ?>">
				</div>
			</div>
		</div>
	</form>

	<!-- Footer Grid: Start -->
	<?php include(__DIR__ . "/../../modules/pie.php"); ?>

</div>
<!-- End: Page Wrap -->

<!--==================== Le jquery ====================-->
<?php include(__DIR__ . "/../../modules/js.php"); ?>
<script type="text/javascript" src="/assets/js/gritter/jquery.gritter.js"></script>
<script src="/assets/js/customrequiest.js"></script>
<script src="/assets/js/jquery.confirm.min.js"></script>
<script src="/assets/js/jquery.tabletojson.js"></script>

<script type="text/javascript">
	$(document).ready(function() {
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

			function limpiarImporte(valor) {
				if (valor === undefined || valor === null || valor === '') return 0;
				valor = String(valor).replace(/\$/g, '').replace(/\s/g, '');

				if (valor.indexOf(',') !== -1 && valor.indexOf('.') !== -1) {
					if (valor.lastIndexOf(',') > valor.lastIndexOf('.')) {
						valor = valor.replace(/\./g, '').replace(',', '.');
					} else {
						valor = valor.replace(/,/g, '');
					}
				} else if (valor.indexOf(',') !== -1) {
					valor = /^-?\d{1,3},\d{3}$/.test(valor) ? valor.replace(/,/g, '') : valor.replace(',', '.');
				}

				valor = parseFloat(valor);
				return isNaN(valor) ? 0 : valor;
			}

			function importeFila(row) {
				return limpiarImporte(row.find('td').eq(5).text());
			}

			function mostrarTotal(total) {
				total = limpiarImporte(total);
				if (total < 0 && Math.abs(total) < 0.01) total = 0;
				$('#total_neto').val(total.toFixed(2));
				$('#div_total_neto').html('$'+total.toFixed(2));
			}

			function recalcularTotalNeto() {
				let total = 0;

				$('#tabla_cotizacion_body tr').not('#cero').each(function() {
					total += importeFila($(this));
				});

				mostrarTotal(total);
				return total;
			}

		/*===========================================================*/
		/*	Function para hacer
		/*===========================================================*/
		$(document).on('click', '.enviar' , function(event) {
			$('#enviar_cotizacion #hacer').val( $(this).data('hacer') );
			console.log('Boton '+$(this).data('hacer'));
		});

		/*===========================================================*/
		/*	Enviar y guardar la cotizacion
		/*===========================================================*/
		$('#enviar_cotizacion').validationEngine("attach", {
			promptPosition : "bottomLeft", 
			autoPositionUpdate : true,
			onValidationComplete: function(form, status){
					if ( status == true ){
						if ( $('#cero').length == false ) {
							recalcularTotalNeto();

							//Serializar la informacion del form
							let form  = $('#enviar_cotizacion').serialize();
							let table = $('#tabla_cotizacion').tableToJSON({
							ignoreColumns: [0],
							headings: ['descripcion','cantidad','unidad','pu','costo']
						});

						//Enviar por ajax
						$.customRequest(
							JSON.stringify(table),
							form,
							{
								onSuccess: function(result) {
									$('#id_cotizacion').val(result.id_cotizacion);
									if ( $('#guardar_nuevo_enviar').length == 0 ) 
										$('#controls_informacion').html('<button type="submit" class="btn btn-lg btn-info enviar" id="guardar" data-hacer="editar">Guardar</button> <button type="submit" class="btn btn-lg btn-primary enviar" id="guardar_enviar" data-hacer="editar_enviar">Enviar PDF</button> <button type="submit" class="btn btn-lg btn-warning enviar" id="guardar_nuevo_enviar" data-hacer="pdf_guardar">PDF y Guardar</button>');
									mensaje(result);

									if (typeof result.archivo != "undefined") window.open(result.archivo);
								}
							}
						);
					}else alert('No se ah agregado nada a la cotización');
				}

			}//onValidationComplete: function(form, status)		
		});

		/*===========================================================*/
		/*	Agregar a la cotizacion
		/*===========================================================*/
		///////////////////////////////////////////////////////////////////
		//Agregar con validationengine
		$('#modal_form_agregar').validationEngine("attach", {
			promptPosition : "bottomLeft", 
			autoPositionUpdate : true,
			onValidationComplete: function(form, status){
				if ( status == true ){
						//Serializar la informacion del form
						let datos_form = $("#modal_form_agregar").serializeArray();
						let hacer 	   = $('#modal_form_agregar #hacer').val();
						let cantidad   = limpiarImporte(datos_form[1].value);
						let precio     = limpiarImporte(datos_form[3].value);
						let costo 	   = cantidad * precio;

					//Agregar a la pagina
					if ( $('#cero').length ) $('#cero').remove(); //Comprobar si se quito row dommie

						let row = $('#tabla_cotizacion_body tr:last');
					switch ( hacer ){
                        case 'agregar':
                            $('#tabla_cotizacion_body').append('<tr></tr>');
                            row = $('#tabla_cotizacion_body tr:last');
                        break;

                        case 'editar':
                            row = $('#tabla_cotizacion_body').find('.editando');
                            row.children().remove();
                        break;
                    }

                    row.append('<td class="center" style="width: 15%;"><div class="btn-group"><button type="button" class="borrar btn btn-default"><span class="glyphicon glyphicon-remove-sign"></span></button><button type="button" class="editar btn btn-default"><span class="glyphicon glyphicon-edit"></span></button></div></td>');
                    row.append('<td class="center">'+datos_form[0].value+'</td>');
                    row.append('<td class="center">'+datos_form[1].value+'</td>');
                    row.append('<td class="center">'+datos_form[2].value+'</td>');
                    row.append('<td class="center">$'+precio.toFixed(2)+'</td>');
                    row.append('<td class="center">$'+costo.toFixed(2)+'</td>');
                    row.removeClass('editando');

                    //Recalcular total neto con todas las filas actuales
                    recalcularTotalNeto();
                    $('#modal_agregar').modal('hide');
				}//if ( status == true )

			}//onValidationComplete: function(form, status)		
		});

		////////////////////////////////////////////////////////////////////
		//Borrar de la cotizacion
		$('#tabla_cotizacion_body').on('click', '.borrar', function(event) {
				event.preventDefault();
				let row = $(this).closest('tr');
					$.confirm({
						text: "Esta seguro que quiere quitar este elemento de la cotización",
						title: "Confirmar",
						confirm: function(button) {
							row.remove();
							if($('#tabla_cotizacion_body > tr').length == 0){
	                            $('#tabla_cotizacion_body').append('<tr id="cero"><td colspan="6" class="center">No se ah agregado nada a la cotización</td></tr>');
	                        }

							recalcularTotalNeto();
						},
				cancel: function(button) {},
				confirmButton: "Borrar",
				cancelButton: "Cancelar",
				post: true
			});
		});

		////////////////////////////////////////////////////////////////////
		//Editar de la cotizacion
		$('#tabla_cotizacion_body').on('click', '.editar', function(event) {
			event.preventDefault();
			let row = $(this).closest('tr');
			let cell = [];

			$(row).find("td").each(function(i,v) {
				cell[i] = $(this).text();
				switch(i){
                    case 4:
                        cell[i] = limpiarImporte(cell[i]);
                    break;
				}
			});

			//Agregar clase para saber que linea editamos
			row.addClass('editando');
			$('#modal_agregar #titleAgregar').html('Editar Concepto');
			$('#modal_agregar #descripcion').val(cell[1]);
			$('#modal_agregar #cantidad').val(cell[2]);
			$('#modal_agregar #unidad').val(cell[3]);
			$('#modal_agregar #precio_unitario').val(cell[4]);
			$('#modal_form_agregar #hacer').val('editar');
			$('#modal_agregar').modal('show');
		});

		////////////////////////////////////////////////////////////////////	
		//Al cerrar el modal
		$('#modal_agregar').on('hidden.bs.modal', function (e) {
			$("#modal_form_agregar").trigger("reset");
			$('#modal_agregar #titleAgregar').html('Agregar a la cotización');
			$('#modal_form_agregar #hacer').val('agregar');
			$('#modal_form_agregar').validationEngine('hideAll');

			let row = $('#tabla_cotizacion_body').find('.editando');
			row.removeClass('editando');
		});
	});
</script>

</body>

</html>
