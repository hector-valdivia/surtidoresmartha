<?php
	session_start();
	include(__DIR__ . "/../../funciones.php");
	
	//Comprobar Inicio de Sesion
	registrado();

	//Conexion de BD
	$con = conecta();

	if($_POST){
	    $keys_post = array_keys($_POST);
	    foreach ($keys_post as $key_post){
	     	$$key_post = limpiar($_POST[$key_post]);
	     	error_log("variable $key_post viene desde $ _POST");
	    }
	}

	switch($hacer) {
	
		case 'insertar':

			//Numero aleatorio para la id del usuario
			$numero_aleatorio = rand(220,100000);

			//Comprobar que el password fue introducido de manera correcta
			if($password != $password2)
				$_SESSION['error'][]= "Los password no son iguales";
		   	else{		   		

				if ( $acceseso == 1 ) {
			   		//Limpia y paso a md5 del pass
					$password  = encriptar( $password );					

					//Buscar informacion para evitar que se repita correos o id's
					$c_usuario = $con->prepare("SELECT COUNT(id_usuario) FROM aio_personal WHERE id_usuario =:id  OR email=:email");
					$c_usuario->bindParam(':id', $id);
					$c_usuario->bindParam(':email', $email);
					$c_usuario->execute();
					$numero = $c_usuario->fetchColumn();
				}else{
					$password  = encriptar( str_pad(letra_rand(10), 10, '0', STR_PAD_LEFT) );
					$password2 = $password;
					$email     = "";
					$nivel     = 0;
					$numero    = 0;
				}			
				
				//Si no se encontraron resultados prosigue
				if($numero == 0){
					
					//Insertar la informacion del usuario en la bd
					$i = $con->prepare("INSERT INTO aio_personal (id_usuario,nombre,apellido,telefono,email,password,nivel,sucursal,salario,categoria) VALUES (:id_usuario,:nombre,:apellido,:telefono,:email,:password,:nivel,:sucursal,:salario,:categoria)");
					$i->bindParam(':id_usuario',$numero_aleatorio);
					$i->bindParam(':nombre',$nombre);
					$i->bindParam(':apellido',$apellido);
					$i->bindParam(':telefono',$telefono);
					$i->bindParam(':email',$email);
					$i->bindParam(':password',$password);
					$i->bindParam(':nivel',$nivel);
					$i->bindParam(':sucursal',$sucursal);
					$i->bindParam(':salario',$salario);
					$i->bindParam(':categoria',$categoria);
			   		$i->execute();
			   		
			   		//Guardar password por si se quiere recuperar
			   		$i_recovery = $con->prepare("INSERT INTO aio_recuperacion (id_usuario,recuperar) VALUES (:id_usuario,:recuperar)");
			   		$i_recovery->bindParam(':id_usuario', $numero_aleatorio);
			   		$i_recovery->bindParam(':recuperar', $password2);
			   		$i_recovery->execute();
					
			   		//Si tiene acceso al sistema se enviara un correo con la informacion vital para el ingreso
					if ( $acceseso == 1 ) {

						$sucursal  = info_sucursal($sucursal); //Informacion general de la sucursal desde la BD

						$titulo = 'Acceso al Sitema de Surtidores Martha'; // subject

						// message
						$mensaje = '
						<html>
							<head>
							  <title>Acceso al Sitema de Surtidoresmartha</title>
							</head>
							<body>
							  <p>
							  	Se a procesado la informacion de '.$nombre."&nbsp;".$apellido.' para el acceso al sistema, su usuario y contraseña son los siguientes:.<br /> 
							  	&nbsp;&nbsp;&nbsp;&nbsp;<b>Usuario:</b> '.$email.'<br />
							  	&nbsp;&nbsp;&nbsp;&nbsp;<b>Contraseña:</b> '.$password2.'<br /><br />

								:::::Sistema Surtidores Martha:::::<br />
							  	<b>Tel.</b> '.$sucursal_direccion['telefono'].'
							  </p>
							</body>
						</html>';

						// Para enviar un correo HTML mail, la cabecera Content-type debe fijarse
						$cabeceras  = 'MIME-Version: 1.0' . "\r\n";
						$cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

						// Cabeceras adicionales
						$cabeceras .= 'From: Sistema Surtidores Martha <sistema@surtidoresmartha.com>' . "\r\n";

						// Mail it
						mail($email, $titulo, $mensaje, $cabeceras);
					}

			   					   
				   	$_SESSION['bien'][] = "Se agrego correctamente el usuario";
				   	
				}else $_SESSION['error'][]="Usuario Existente";
		   }		   

		break;
		
		
		case 'editar':

			//Buscar si el usuario existe
			$c = $con->prepare("SELECT COUNT(id_usuario) FROM aio_personal WHERE id_usuario=:id");
			$c->bindParam(':id',$id);
			$c->execute();

			if ( $c->fetchColumn() == 0 ) { //Comprobar que existe el usuario
				header("location:table.php");
			}else{

				if( !empty($password) && !empty($password2) && $password == $password2 ){ //Bloque cuando se requiere cambiar la contraseña
					$password  = encriptar($password);
					$password2 = limpiar($password2);
				}else{ //Bloque donde no se requiere cambio de contraseña
					$b = $con->prepare("SELECT password,recuperar FROM aio_personal INNER JOIN aio_recuperacion ON aio_recuperacion.id_usuario = aio_personal.id_usuario WHERE aio_personal.id_usuario=:id");
					$b->bindParam(':id',$id);
					$b->execute();
					$r = $b->fetchObject();

					//Password anteriores del usuario
					$password  = $r->password;
					$password2 = $r->recuperar;
				}

				//Si no tiene o se le quita acceseso al sistema, se limpia el campo de correo y se coloca un password random
				if( $acceseso == 0 ){ 
					$nivel = 0;
					$email = '';
					$password  = encriptar( str_pad(letra_rand(10), 10, '0', STR_PAD_LEFT) );
					$password2 = $password;
				}

				///Actualizar BD con la nueva informacion del personal
				$u = $con->prepare("UPDATE aio_personal SET
					nombre=:nombre,
					apellido=:apellido,
					telefono=:telefono,
					email=:email,
					password=:password,
					nivel=:nivel,
					sucursal=:sucursal,
					salario=:salario,
					categoria=:categoria
					WHERE id_usuario=:id
				");
				$u->bindParam(':nombre',$nombre);
				$u->bindParam(':apellido',$apellido);
				$u->bindParam(':telefono',$telefono);
				$u->bindParam(':email',$email);
				$u->bindParam(':password',$password);
				$u->bindParam(':nivel',$nivel);
				$u->bindParam(':sucursal',$sucursal);
				$u->bindParam(':salario',$salario);
				$u->bindParam(':categoria',$categoria);
				$u->bindParam(':id',$id);
				$u->execute();

				//Actualizar password por si se quiere recuperar
				$u_recovery = $con->prepare("UPDATE aio_recuperacion SET recuperar=:recuperar WHERE id_usuario=:id_usuario");
				$u_recovery->bindParam(':recuperar', $password2);
				$u_recovery->bindParam(':id_usuario', $id);
				$u_recovery->execute();

				//Mensaje de exito				
				$_SESSION['bien'][] = "La actualizacion del usuario fue correcta";
			}
		   
		break;			
		
		case 'borrar':		    	
			if(is_numeric($id)){
				$d = $con->prepare("DELETE FROM aio_personal WHERE id_usuario=:id");
				$d->bindParam(':id', $id);
				$d->execute();
				
				$d = $con->prepare("DELETE FROM aio_recuperacion WHERE id_usuario=:id");
				$d->bindParam(':id', $id);
				$d->execute();
				
				$_SESSION['bien'][] = "El usuario fue eliminado";

			}else $_SESSION['error'][] = "Se encontraron errores en los formularios contacte al administrador";								
							
		break;
	}

	header("location: table.php");
?>