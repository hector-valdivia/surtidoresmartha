<?php
session_start();
$user = addslashes($_POST['user']); /* Recibimos y eliminamos caracteres raros de $user*/
$user = strip_tags($user);          /* Eliminamos tags de codigo en $user*/
$user = htmlspecialchars($user);    /* Eliminamos codigo html de $user */
$user = utf8_decode($user);         /* Convertimos a utf8 $user*/
$pass = $_POST['pass'];             /* Recibimos contenido de $pass*/  

 
if( empty($user) || empty($pass) ) $error .="<b>Usuario o Password</b> esta vacio\n"; 

   
if( isset($error) ){ /* Validamos si hay error de tal manera evitamos que todo el codigo se ejecute. */	
	$_SESSION['error'][] = $error;
 	header("Location: ../main"); /* Si hay error nos manda a defult.php carga la variable $error y la muestra */
}else{		
	
	include('../funciones.php');

	//Conexion de BD
	$con = conecta();
 
 	//VALIDAMOS PRIMERO EL USUARIO 	
 	$b_usuario = $con->prepare("SELECT count(id_usuario) FROM aio_personal WHERE id_usuario=:id OR email=:email");
 	$b_usuario->bindParam(':id',$user);
 	$b_usuario->bindParam(':email',$user);
 	$b_usuario->execute();
	$numero = $b_usuario->fetchColumn();
 	 	
 	if( $numero==0 ){
 		$_SESSION['error'][]= "No existe el usuario";
 		header("Location: ../main");
 	}else{
 		
	 	//VALIDAMOS PASSWORD
	 	$key = encriptar($pass);
	 		  	
	 	$b_pass = $con->prepare("SELECT count(password) FROM aio_personal WHERE password=:pass AND (id_usuario=:id OR email=:email)");
	 	$b_pass->bindParam(':id',$user);
	 	$b_pass->bindParam(':email',$user);
	 	$b_pass->bindParam(':pass',$key);
	 	$b_pass->execute();
	 	$numero = $b_pass->fetchColumn();
	 	
	 	if($numero==0){ 
			$_SESSION['error'][]= "Password incorrecto";	
			header("Location: ../main");
 		}else{
			//NIVEL DE USUARIO 			
 			$b_usuario = $con->prepare("SELECT id_usuario,nivel FROM aio_personal WHERE password=:pass AND (id_usuario=:id OR email=:email)");
 			$b_usuario->bindParam(':id',$user);
 			$b_usuario->bindParam(':email',$user);
 			$b_usuario->bindParam(':pass',$key);
 			$b_usuario->execute();
 			$b_usuario->bindColumn('id_usuario',$id);
 			$b_usuario->bindColumn('nivel',$nivel);
 			$b_usuario->fetch();
			$_SESSION['id'] = encriptar($id);

			switch ($nivel) {
				case 1:
					header('location: ../dashboard');
				break;

				case 2:
					header('location: ../manage_ordenes/table.php');
				break;				
				
				default:
					$_SESSION['error'][]= "No tiene acceso";
					header("Location: ../main");
				break;
			}
 		}
	}	
}
 
$con = null;

?>