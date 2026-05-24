<?php
session_start();
include('../funciones.php');

//registrado();

//Conexion de BD
$con = conecta();

if($_POST){
	$keys_post = array_keys($_POST);
	foreach ($keys_post as $key_post){
		$$key_post = $_POST[$key_post];
	}
}

switch ($hacer){
	case 'insertar':		
		$categoria = limpiar($categoria);
		
		if( !empty($categoria) ){
			
			$i = $con->prepare("INSERT INTO aio_trabajos (trabajos) VALUES (:trabajos)");			
			$i->bindParam(':trabajos', $categoria);
			$i->execute();

		}else $_SESSION['error'] = 'El nombre del trabajo no puede venir vacio';
		
		header('Location:table.php');
	break;
	
	case 'editar':
		$categoria = limpiar($categoria);
		$id 	   = limpiar($id);
		
		if( !empty($categoria) ){	
			$u = $con->prepare("UPDATE aio_trabajos SET trabajos=:trabajos WHERE id=:id");
			$u->bindParam(':trabajos', $categoria);
			$u->bindParam(':id', $id);
			$u->execute();

		}else $_SESSION['error'] = 'El nombre del trabajo no puede venir vacio';

		header('Location:table.php');
	break;
	
	case 'eliminar':
		$id = limpiar($id);
		$d = $con->prepare("DELETE FROM aio_trabajos WHERE id=:id");
		$d->bindParam(':id', $id);
		$d->execute();
		
		header('Location:table.php');
	break;	
	
	default:
		header('Location:table.php');
	break;	
}
?>