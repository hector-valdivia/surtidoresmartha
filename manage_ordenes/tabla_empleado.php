	<!-- ==================== Tabla empleados ==================== -->	
	<div class="row">
		<div class="col-lg-12">
			<!-- Box Header: Start -->
			<div class="box_top">
				<h2>Costo de la Orden de trabajo</h2>
				<ul class="sorting">
					<li><a class="btn" data-toggle="modal" data-target="#modal_agregar_empleado">Agregar trabajador</a></li>
				</ul>
			</div>
			<!-- Box Header: End -->
			
			<!-- Box Content: Start -->
			<div class="box_content">
				<table>
					<thead>
						<tr>
							<th class="center" width="40"></th>
							<th class="center">Empleado</th>
							<th class="center">Categoria</th>
							<th class="center">T.Horas</th>
							<th class="center">Salario</th>
							<th class="center">Costo</th>								
						</tr>
					</thead>
					<tbody id="tabla_empleado">
						<tr id="cero">
							<td colspan="6">Agregue un empleado a la orden de trabajo</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="5" style="text-align:right;"><b>Total neto:</b></td>
							<td class="align_left center"><b id="div_total_neto"></b></td>
							<input type="hidden" name="total_neto" id="total_neto" value="0">
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>

	<!-- ==================== Modal Agregar empleado ==================== -->
	<div id="modal_agregar_empleado" class="modal fade" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="btn btn-default close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title" id="myModalLabel">Agregar Empleado</h4>
				</div>
				<form id="modal_form_agregar_empleado" method="POST">
					<div class="modal-body form">
						<div class="form-group">
							<label>Empleado</label>
							<select name="empleado" id="empleado" class="form-control validate[required]">
								<option value="" selected="true">Seleccione</option>
								<?php
									$b = $con->prepare("SELECT id_usuario,nombre,apellido,categoria,salario FROM aio_personal WHERE id!=1");
									$b->execute();
									$opciones = null; //Variable donde se generan las categorias de cada empleado
									while ( $r = $b->fetchObject() ){

										if ( !empty($r->categoria) ) {

											echo '<option value="'.$r->id_usuario.'" data-nombre="'.$r->apellido.' '.$r->nombre.'" data-precio="'.$r->salario.'">'.$r->apellido.' '.$r->nombre.'</option>';
											$categoria = explode(',',$r->categoria);
											foreach ($categoria as $cat) {
												$opciones.= '<option value="'.$cat.'" class="'.$r->id_usuario.'">'.$cat.'</option>';
											}
										}
										
									}
								?>
							</select>
						</div>

						<div class="form-group">
							<label>Categoria</label>
							<select name="categoria" id="categoria" class="form-control validate[required]">
								<option value="" selected="true">Seleccione</option>
								<?php echo $opciones; ?>
							</select>
						</div>

						<div class="form-group">
							<label>Horas trabajadas</label>
							<input type="text" name="horas" id="horas" class="form-control validate[required]">
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default btn-flat" data-dismiss="modal">Cancelar</button>
						<button type="submit" id="enviar" class="btn btn-primary btn-flat">+ Agregar</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	
	<script>
		/*===========================================================*/
		/*	Agregar Empleado
		/*===========================================================*/
		////////////////////////////////////////////////////////////////////	
		//Agregar con validationengine
		$("#modal_form_agregar_empleado").validationEngine("attach", {
			promptPosition : "bottomLeft", 
			autoPositionUpdate : true,
			onValidationComplete: function(form, status){				

				if ( status == true ){
					//Serializar la informacion del form
					var datos_form = $("#modal_form_agregar_empleado").serializeArray();
					var $empleado  = $('#modal_form_agregar_empleado #empleado option:selected');
					var costo 	   = $empleado.data('precio')*datos_form[2].value;
					var total_neto = parseFloat( $('#total_neto').val() ) + parseFloat( costo );

					//Agregar a la pagina
					if ( $('#cero').length ) $('#cero').remove(); //Comprobar si se quito row dommie

					//Agregar a la tabla
					$('#tabla_empleado').append('<tr id="'+datos_form[0].value+'"></tr>');
					$('#tabla_empleado tr:last').append('<td class="center"><a class="borrar_empleado" data-empleado="'+datos_form[0].value+'" href="#"><span class="glyphicon glyphicon-remove-sign"></span></a></td>');
					$('#tabla_empleado tr:last').append('<td class="center">'+$empleado.data('nombre')+'</td>');
					$('#tabla_empleado tr:last').append('<td class="center">'+datos_form[1].value+'</td>');
					$('#tabla_empleado tr:last').append('<td class="center">'+datos_form[2].value+' hrs</td>');
					$('#tabla_empleado tr:last').append('<td class="center">$'+$empleado.data('precio').toFixed(2)+'</td>');					
					$('#tabla_empleado tr:last').append('<td class="center">$'+costo.toFixed(2)+'</td>');

					//Agregar a la tabla
					$('#total_neto').val(total_neto.toFixed(2));
					$('#div_total_neto').html('$'+total_neto.toFixed(2));

					$('#modal_agregar_empleado').modal('hide');
				}//if ( status == true )

			}//onValidationComplete: function(form, status)
		});

		////////////////////////////////////////////////////////////////////
		//Borrar Empleado agregado
		$('#tabla_empleado').on('click', '.borrar_empleado', function(event) {
			event.preventDefault();
			var empleado = $(this).data('empleado');
			$.confirm({
				text: "Esta seguro que quiere quitar este empleado de la cotización",
				title: "Confirmar",
				confirm: function(button) {
					total_neto = parseFloat( $('#total_neto').val() ) - parseFloat($('#'+empleado+" td:eq(5)").text().replace('$', ''));

					//Agregar a la tabla
					$('#total_neto').val(total_neto.toFixed(2));
					$('#div_total_neto').html('$'+total_neto.toFixed(2));

					$('#'+empleado).remove();
					if( $('#tabla_empleado > tr').length == 0 ) 
						$('#tabla_empleado').append('<tr id="cero"><td colspan="6">Agregue un empleado a la orden de trabajo</td></tr>');			
				},
				cancel: function(button) {},
				confirmButton: "Borrar",
				cancelButton: "Cancelar",
				post: true
			});
		});

		////////////////////////////////////////////////////////////////////	
		//Al cerrar el modal
		$('#modal_agregar_empleado').on('hidden.bs.modal', function (e) {
			$("#modal_form_agregar_empleado").trigger("reset");
			$("#modal_form_agregar_empleado #categoria").attr('disabled', 'disabled');
			$('#modal_form_agregar_empleado').validationEngine('hideAll');
		});
	</script>