<?php
header('Content-Type: text/html; charset=UTF-8'); 

session_start();
include(__DIR__ . "/../../funciones.php");

registrado();

//Conexion de BD
$con = conecta();

$direccion = "table.php";

if ( $_POST ) {
	$keys_post = array_keys($_POST);
	foreach ($keys_post as $key_post){
		$$key_post = limpiar($_POST[$key_post]);
	}
}

function findOrder($orden_id)
{
    $con = conecta();
    $b = $con->prepare("SELECT * FROM aio_orden WHERE id=:id");
    $b->bindParam(':id',$orden_id);
    $b->execute();

    if ($b->rowCount() != 1) {
        $_SESSION['error'][] = "La orden que se selecciono no existe";
        header('location:table.php');
        exit;
    }

    return $b->fetchObject();
}

switch ( $hacer ) {
	case 'insertar':

		//Informacion de la sucursal del usuario logueado
		//$sucursal = sucursal($_SESSION['id']);

		//////////////////////////////////////////////////////////////////////////////////
		//Cantidad de pedidos hechos y generar el folio en base a esos folios hechos
		//////////////////////////////////////////////////////////////////////////////////
		$c = $con->prepare("SELECT COUNT(id) FROM aio_orden WHERE sucursal=:sucursal");
		$c->bindParam(':sucursal',$sucursal);
		$c->execute();
		$numero = $c->fetchColumn();
		//El folio incluye la ID de la sucursal y el numero de flios hechos +1
		$folio = $sucursal.'-'.str_pad($numero+1, 4, 0, STR_PAD_LEFT);
		//////////////////////////////////////////////////////////////////////////////////

		//Si no se uso una empresa registrada
		if ( empty($empresa_registrada) ){

			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			///Generar ID unico para el cliente y comprobar que no exista en la base de datos
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			$c = $con->prepare("SELECT COUNT(id) FROM aio_cliente");
			$c->execute();
			$numero = $c->fetchColumn();

			do{
				$id_cliente = substr($sucursal	, 3).letra_rand(1).rand(0,9).rand(0,9).str_pad($numero+1, 4, 0, STR_PAD_LEFT);
				$c = $con->prepare("SELECT COUNT(id) FROM aio_cliente WHERE id_cliente=:id");
				$c->bindParam(':id',$id_cliente);
				$c->execute();
				$id = $c->fetchColumn();

			}while ( $id != 0);
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			///Insertar en la base de datos
			$i = $con->prepare("INSERT INTO aio_cliente 
				(id_cliente,cliente,telefono,celular,email,calle,noext,interior,colonia,estado,municipio,cp,razon_social,rfc) 
				VALUES 
				(:id_cliente,:cliente,:telefono,:celular,:email,:calle,:noext,:interior,:colonia,:estado,:municipio,:cp,:razon_social,:rfc)");
			$i->bindParam(':id_cliente',$id_cliente);
			$i->bindParam(':cliente',$empresa);
			$i->bindParam(':telefono',$tel);
			$i->bindParam(':celular',$cel);
			$i->bindParam(':email',$email);
			$i->bindParam(':calle',$calle);
			$i->bindParam(':noext',$noext);
			$i->bindParam(':interior',$int);
			$i->bindParam(':colonia',$colonia);
			$i->bindParam(':estado',$estado);
			$i->bindParam(':municipio',$municipio);
			$i->bindParam(':cp',$cp);
			$i->bindParam(':razon_social',$razonsocial);
			$i->bindParam(':rfc',$rfc);
			$i->execute();

			//Mensaje de exito
			$_SESSION['bien'][] = "Cliente <i>".$empresa."</i> agregado correctamente";

		}else{ //Si se uso una empresa registrada
			//Para comprobar que el cliente existe
			$c = $con->prepare("SELECT COUNT(id_cliente) FROM aio_cliente WHERE id_cliente=:id");
			$c->bindParam(':id',$empresa_registrada);
			$c->execute();
			$numero = $c->fetchColumn();

			//Si no existe la id del cliente regresa a la tabla de ordenes y muestra un error
			if ($numero != 1) {
				//Regresar a la tabla
				header('location:table.php');

				//Mensaje de error
				$_SESSION['error'][] = "El cliente que se selecciono no existe";
			}else{
                $id_cliente = $empresa_registrada; //Si existe se coloca la id en la variable id de cliente
            }
		}

		//Para evitar problemas no se puede escribir en la BD si no existe algun tipo de id de cliente
		if( !empty($id_cliente) ){
			$fecha_orden = date('Y-m-d g:i:s');

			///Formato de escritura
			$trabajo_solicitado 		= htmlentities( addslashes($_POST['trabajo_solicitado']) );
			$trabajo_realizar			= htmlentities( addslashes($_POST['trabajo_realizar']) );
			$diagnostico_observaciones 	= htmlentities( addslashes($_POST['diagnostico_observaciones']) );

			//Insertar en BD la orden
			$i = $con->prepare("INSERT INTO aio_orden 
				(folio,id_cliente,departamento,sucursal,solicitante,fecha_deseada,fecha_orden,trabajo_solicitado,trabajo_realizar,planeador,aprobo,diagnostico_observaciones,cantidad_mano_obra,prioridad,turno,estado_orden)
				VALUES 
				(:folio,:id_cliente,:departamento,:sucursal,:solicitante,:fecha_deseada,:fecha_orden,:trabajo_solicitado,:trabajo_realizar,:planeador,:aprobo,:diagnostico_observaciones,1,:prioridad,:turno,'abierto')
			");
			$i->bindParam(':folio',$folio);
			$i->bindParam(':id_cliente',$id_cliente);
			$i->bindParam(':departamento',$departamento);
			$i->bindParam(':sucursal',$sucursal);
			$i->bindParam(':solicitante',$solicitante);
			$i->bindParam(':fecha_deseada',$fecha_deseada);
			$i->bindParam(':fecha_orden',$fecha_orden);
			$i->bindParam(':trabajo_solicitado', $trabajo_solicitado);
			$i->bindParam(':trabajo_realizar', $trabajo_realizar);
			$i->bindParam(':planeador',$planeador);
			$i->bindParam(':aprobo',$responsable);
			$i->bindParam(':diagnostico_observaciones', $diagnostico_observaciones);
			$i->bindParam(':prioridad',$prioridad);
			$i->bindParam(':turno',$turno);
			$i->execute();
            $id_orden = $con->lastInsertId();

			$i = $con->prepare("INSERT INTO aio_orden_personal 
                (orden_id,id_personal,categoria,empezo,termino,costo,dia) 
				VALUES
				(:orden_id,:id_personal,'Encargado',:empezo,:termino,0,:dia)");
			$i->bindParam(':orden_id',$id_orden);
			$i->bindParam(':id_personal',$planeador);
			$i->bindParam(':empezo',$fecha_orden);
			$i->bindParam(':termino',$fecha_deseada);
			$i->bindParam(':dia',$fecha_orden);
			$i->execute();

			//Mensaje de exito
			$_SESSION['bien'][] = 'Fue creado sin problemas la orden <b>'.$folio.'</b>';
		}
	break;

	case 'info_pedido':
        $orden = findOrder($orden_id);

		///Formato de escritura
		$trabajo_solicitado 		= htmlentities( addslashes($_POST['trabajo_solicitado']) );
		$trabajo_realizar			= htmlentities( addslashes($_POST['trabajo_realizar']) );
		$diagnostico_observaciones 	= htmlentities( addslashes($_POST['diagnostico_observaciones']) );

		$u = $con->prepare("UPDATE aio_orden SET 
            trabajo_solicitado=:trabajo_solicitado, trabajo_realizar=:trabajo_realizar, diagnostico_observaciones=:diagnostico_observaciones, estado_orden=:estado_orden 
            WHERE id=:id");
		$u->bindParam(':trabajo_solicitado',$trabajo_solicitado);
		$u->bindParam(':trabajo_realizar',$trabajo_realizar);
		$u->bindParam(':diagnostico_observaciones',$diagnostico_observaciones);
		$u->bindParam(':estado_orden',$estado_orden);
		$u->bindParam(':id',$orden->id);
		$u->execute();

		$direccion = "info.php?id=".encriptar($order->folio);

		$_SESSION['bien'][] = 'Se actualizo la orden <b>'.$orden->id.'</b>';
	break;

	case 'material':
        $orden = findOrder($orden_id);
		$cantidad = cleanNumber($cantidad);
		$costo = cleanNumber($costo);

		$i = $con->prepare("INSERT INTO aio_orden_material 
            (orden_id, material, cantidad, unidad, costo) 
            VALUES
            (:orden_id,:material,:cantidad,:unidad,:costo)");
		$i->bindParam(':orden_id',$orden->id);
		$i->bindParam(':material', $material);
		$i->bindParam(':cantidad',$cantidad);
		$i->bindParam(':unidad',$unidad);
		$i->bindParam(':costo',$costo);
		$i->execute();

		$direccion = "info.php?id=".encriptar($orden->id);
		$_SESSION['bien'][] = 'Fue ingresado el material a la orden <b>'.$orden->folio.'</b>';
	break;

	case 'trabajador':
        $orden = findOrder($orden_id);
		$horas = cleanNumber($horas);
		$b = $con->prepare("SELECT salario FROM aio_personal WHERE id_usuario=:planeador AND id!=1");
		$b->bindParam(':planeador',$trabajador);
		$b->execute();
		$r = $b->fetchObject();
		$costo = cleanNumber($r->salario)*$horas;

		$i = $con->prepare("INSERT INTO aio_orden_personal 
            (orden_id, id_personal, categoria, dia, horas, costo, empezo, termino) 
            VALUES 
            (:orden_id,:id_personal,:categoria,:dia,:horas,:costo, :empezo, :termino)");
		$i->bindParam(':orden_id',$orden->id);
		$i->bindParam(':id_personal',$trabajador);
		$i->bindParam(':categoria',$categoria);
		$i->bindParam(':empezo',$dia);
		$i->bindParam(':termino',$dia);
		$i->bindParam(':dia',$dia);
		$i->bindParam(':horas',$horas);
		$i->bindParam(':costo',$costo);
		$i->execute();

		$direccion = "info.php?id=".encriptar($orden->id);

		//Mensaje de exito
		$_SESSION['bien'][] = "Se actualizo la informacion correspondiente a la orden <i>".$orden->folio."</i>";
	break;

	case 'herramienta':
        $orden = findOrder($orden_id);
		$cantidad = cleanNumber($cantidad);
		$i = $con->prepare("INSERT INTO aio_orden_herramienta (orden_id, herramienta, cantidad) VALUES (:order_id,:herramienta,:cantidad)");
		$i->bindParam(':order_id',$orden->id);
		$i->bindParam(':herramienta',$herramienta);
		$i->bindParam(':cantidad',$cantidad);
		$i->execute();

		$direccion = "info.php?id=".encriptar($orden->id);

		//Mensaje de exito
		$_SESSION['bien'][] = 'Fue ingresado la herramienta a la orden <b>'.$orden->folio.'</b>';
	break;

	default:
		echo 'Ready the beer';
	break;
}


$con = '';
header("location:$direccion");