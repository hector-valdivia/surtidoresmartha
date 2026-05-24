<?php 

	include('funciones.php');	

	//Conexion de BD
	$con = conecta();

	$b = $con->prepare("SELECT folio,id_cliente,sucursal,hasta,domingos FROM aio_pedidos WHERE estado_pedido='abierto'");
	$b->execute();

	while ( $r = $b->fetchObject() ) {		
		
		$duracion = diashabiles( contardias( date('Y-m-d H:i:s'),$r->hasta ), $r->domingos );

		if ($duracion['vencido'] == 1 ){			
			$u = $con->prepare("UPDATE aio_pedidos SET estado_pedido='vencido' WHERE folio=:folio");
			$u->bindParam(':folio',$r->folio);
			$u->execute();
		}

		echo date('Y-m-d g:i:s').' '.$r->hasta.'<br/>';
		if ($duracion['vencido'] == 1) echo 'Vencido <br/>';
		else echo 'No ha Vencido <br/>';
		
		echo $duracion['duracion'].' duracion <br/>';
		echo $duracion['horas'].'<br/><br/>';

		if ( $duracion['vencido'] == 0 && $duracion['horas'] == 24 ){
			$cliente   = info_cliente($r->id_cliente);
			$sucursal  = info_sucursal($r->sucursal);
			$sucursal_direccion = unserialize($sucursal->informacion);

			$b_p = $con->prepare("SELECT total_producto FROM aio_pedidos_productos WHERE folio=:folio");
			$b_p->bindParam(':folio',$r->folio);
			$b_p->execute();

			$renta_diaria = 0;
			while ( $r_p = $b_p->fetchObject() ) $renta_diaria+= $r_p->total_producto;
			
			$para   = $cliente->email; //Correo de envio
			$titulo = 'Recordatorio: pronto finalizara la renta'; // subject

			// message
			$mensaje = '
			<html>
				<head>
				  <title>Recordatorio: pronto finalizara la renta</title>
				</head>
				<body>
				  <p>				  	
				  	Su renta vencera el dia <i>'.fecha_es($r->hasta).'</i>, en estos momentos faltan 24 horas para su vencimiento.<br /> 
				  	Recomendamos para no generar recargos entregar el equipo con tiempo, recuerde que su renta diaria es de <b>$'.dinero($renta_diaria).'</b>.<br /><br />

				  	Atencion a clientes de Andamios Santa Fe<br />
				  	<b>Tel.</b> '.$sucursal_direccion['telefono'].'
				  </p>
				</body>
			</html>';

			// Para enviar un correo HTML mail, la cabecera Content-type debe fijarse
			$cabeceras  = 'MIME-Version: 1.0' . "\r\n";
			$cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

			// Cabeceras adicionales
			$cabeceras .= 'From: Atencion a clientes Andamios Sata Fe <atencionclientes@andamiossantafe.com.mx>' . "\r\n";

			// Mail it
			mail($para, $titulo, $mensaje, $cabeceras);
		}
		
	}
?>