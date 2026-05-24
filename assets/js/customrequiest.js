// Plugin que nos ayudará a manejar las preticiones.
$.customRequest = function(action, data, events) {
	// Creamos los eventos por defecto.
	var events = $.extend({
		onSuccess:	function() {}
	}, events);
	
	// Realizamos la petición.
	$.ajax({
		url:		'ajax.php?action=' + action,
		dataType:	'JSON',
		type:		'POST',
		data:		data,
		beforeSend:	function(){
			// Eliminamos todos los mensajes activos y activamos el mensaje de carga.
			if ( $('#modal_loading').length == 0 ){
				$('body').append('<div id="modal_loading" class="modal fade colored-header warning"><div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="text-center"><i class="fa fa-spinner fa-spin" style="font-size:50px;"></i><h4>Cargando!</h4><p>Se esta cargando su petición</p></div></div></div></div></div>');
			}

			$('#modal_loading').modal('show');
		},
		error: function(jqXHR){
			// Eliminamos todos los mensajes y mostramos el error.
			$.gritter.add({
				title: 'Warning',
				text: jqXHR.responseText,
				class_name: 'warning',
				time: 80000
			});
		},
		success: function(result){
			if ( result.r ) events.onSuccess(result);
			else{
				$.gritter.add({
					title: 'Warning',
					text: result.mensaje,
					class_name: 'warning',
					time: 80000
				});
			}
		},
		complete: function(){
			// Ocultamos el mensaje de carga.
			$('#modal_loading').modal('hide');
		}
	});
}

