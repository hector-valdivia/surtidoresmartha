<?php

session_start();
include('../funciones.php');

//Comprobar Inicio de Sesion
registrado();

//Conexion de BD
$con = conecta();

if( $_POST ){
    $keys_post = array_keys($_POST);
    foreach ($keys_post as $key_post){
     	$$key_post = limpiar($_POST[$key_post]);
    }
}

switch ($hacer) {

	case 'insertar':

		///Obtener de la secion iniciada la sucursal
		$sucursal = sucursal($_SESSION['id']);

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		///Generar ID unico para el cliente y comprobar que no exista en la base de datos
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$c = $con->prepare("SELECT COUNT(id) FROM aio_cliente");
		$c->execute();
		$numero = $c->fetchColumn();

		do{
			$id_cliente = substr($sucursal->id_sucursal	, 3).letra_rand(1).rand(0,9).rand(0,9).str_pad($numero+1, 4, 0, STR_PAD_LEFT);
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
			
	break;

	case 'editar':
		$b = $con->prepare("SELECT salario FROM aio_personal WHERE id_usuario=:planeador AND id!=1");
		$b->bindParam(':planeador',$trabajador);
		$b->execute();
		$r = $b->fetchObject();
		$costo = $r->salario*$horas;

		$i = $con->prepare("INSERT INTO aio_orden_personal (folio,id_personal,categoria,dia,horas,costo) VALUES (:folio,:id_personal,:categoria,:dia,:horas,:costo)");
		$i->bindParam(':folio',$folio);
		$i->bindParam(':id_personal',$trabajador);
		$i->bindParam(':categoria',$categoria);
		$i->bindParam(':dia',$dia);
		$i->bindParam(':horas',$horas);
		$i->bindParam(':costo',$costo);
		$i->execute();

		$direccion = "editar?id=".encriptar($folio);
		
		//Mensaje de exito
		$_SESSION['bien'][] = "Se actualizo la informacion correspondiente a la orden <i>".$folio."</i>";
	break;

	case 'material':
		$i = $con->prepare("INSERT INTO aio_orden_material (folio,material,costo) VALUES (:folio,:material,:costo)");
		$i->bindParam(':folio',$folio);
		$i->bindParam(':material',$material);
		$i->bindParam(':costo',$costo);
		$i->execute();

		$direccion = "editar?id=".encriptar($folio);

		//Mensaje de exito
		$_SESSION['bien'][] = 'Fue ingresado el material a la orden <b>'.$folio.'</b>';
	break;	

	case 'borrar':

		//Borrar de la base de datos
		$d = $con->prepare("DELETE FROM aio_cliente WHERE id_cliente=:id");
		$d->bindParam(':id',$id);
		$d->execute();

		//Mensaje de exito
		$_SESSION['bien'][] = "Cliente eliminado de la base de datos";
	break;

}

header("location:$direccion");

$con = null;
?>
