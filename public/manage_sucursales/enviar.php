<?php

session_start();
include(__DIR__ . "/../../funciones.php");

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

		$c_usuarios = $con->prepare("SELECT COUNT(id) FROM aio_sucursal");
		$c_usuarios->execute();
		$numero =  str_pad($c_usuarios->fetchColumn()+1, 2, "0", STR_PAD_LEFT);

		$id_sucursal = 'CUU'.$numero;

		///Insertar en la base de datos
		$i = $con->prepare("INSERT INTO aio_sucursal 
			(id_sucursal,nombre,telefono,celular,calle,noext,interior,colonia,estado,municipio,cp,razon_social,rfc) 
			VALUES 
			(:id_sucursal,:nombre,:telefono,:celular,:calle,:noext,:interior,:colonia,:estado,:municipio,:cp,:razon_social,:rfc)");
		$i->bindParam(':id_sucursal',$id_sucursal);
		$i->bindParam(':nombre',$nombre);
		$i->bindParam(':telefono',$tel);
		$i->bindParam(':celular',$cel);
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
		$_SESSION['bien'][] = "Sucursal <i>".$nombre."</i> agregada correctamente";
			
	break;

	case 'editar':
		$u = $con->prepare("UPDATE aio_sucursal 
			SET
			id_sucursal=:id_sucursal,
			nombre=:nombre,
			telefono=:telefono,
			celular=:celular,
			calle=:calle,
			noext=:noext,
			interior=:interior,
			colonia=:colonia,
			estado=:estado,
			municipio=:municipio,
			cp=:cp,
			razon_social=:razon_social,
			rfc=:rfc
			WHERE id_sucursal=:id_sucursal");		
		$u->bindParam(':nombre',$nombre);
		$u->bindParam(':telefono',$tel);
		$u->bindParam(':celular',$cel);
		$u->bindParam(':calle',$calle);
		$u->bindParam(':noext',$noext);
		$u->bindParam(':interior',$int);
		$u->bindParam(':colonia',$colonia);
		$u->bindParam(':estado',$estado);
		$u->bindParam(':municipio',$municipio);
		$u->bindParam(':cp',$cp);
		$u->bindParam(':razon_social',$razonsocial);
		$u->bindParam(':rfc',$rfc);
		$u->bindParam(':id_sucursal',$id_sucursal);
		$u->execute();

		//Mensaje de exito
		$_SESSION['bien'][] = "Se actualizo la informacion correspondiente al Cliente <i>".$empresa."</i>";
	break;

	case 'borrar':

		//Borrar de la base de datos
		$d = $con->prepare("DELETE FROM aio_sucursal WHERE id_sucursal=:id");
		$d->bindParam(':id',$id);
		$d->execute();

		//Mensaje de exito
		$_SESSION['bien'][] = "Cliente eliminado de la base de datos";
	break;

}

header("location:table.php");

$con = null;
?>
