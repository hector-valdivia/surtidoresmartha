<?php
	include(__DIR__ . '/bar.php');

	/////////////////////////////////////////////////////////////////////
	define('_BASE_URL', $aioWeb);
	
	/////////////////////////////////////////////////////////////////////
	date_default_timezone_set('America/Chihuahua');
	/////////////////////////////////////////////////////////////////////
	
	//Session para que el navegador de archivos funcione
	$_SESSION['KCFINDER']['disabled'] = false;

	/////////////////////////////////////////////////////////////////////
	/* Funcion fecha español*/
	/////////////////////////////////////////////////////////////////////
	function fecha_es($fecha){

		if ( strpos($fecha, ' ') === true ){ 
			$fecha = explode(' ',$fecha);
			$hora  = ' '.$fecha[1];
			$fecha = $fecha[0];
		}else $hora = '';

		$fecha = explode('-',$fecha);

		$mes = [
			'01' => 'Enero',
			'02' => 'Febrero',
			'03' => 'Marzo',
			'04' => 'Abril',
			'05' => 'Mayo',
			'06' => 'Junio',
			'07' => 'Julio',
			'08' => 'Agosto',
			'09' => 'Septiembre',
			'10' => 'Octubre',
			'11' => 'Noviembre',
			'12' => 'Diciembre',
		];

		$fecha = $fecha[2].'/'.$mes[$fecha[1]].'/'.$fecha[0].$hora;

		return $fecha;
	};

	/////////////////////////////////////////////////////////////////////
	/* Funcion fecha español*/
	/////////////////////////////////////////////////////////////////////
	function fecha_espanol($fecha){
		$fecha = explode('-',$fecha);

		$mes = [
			'01' => 'Enero',
			'02' => 'Febrero',
			'03' => 'Marzo',
			'04' => 'Abril',
			'05' => 'Mayo',
			'06' => 'Junio',
			'07' => 'Julio',
			'08' => 'Agosto',
			'09' => 'Septiembre',
			'10' => 'Octubre',
			'11' => 'Noviembre',
			'12' => 'Diciembre',
		];

		$fecha = $fecha[2].'/'.$mes[$fecha[1]].'/'.$fecha[0];

		return $fecha;
	};

	/////////////////////////////////////////////////////////////////////
	/* Funcion contar los productos que no se prestaron */
	/////////////////////////////////////////////////////////////////////
	function productos_sin_prestar($producto,$inventario){		
		$con = conecta();

		$b_r = $con->prepare("SELECT * FROM 
			aio_pedidos_productos INNER JOIN aio_pedidos 
			ON aio_pedidos_productos.folio = aio_pedidos.folio 
			WHERE producto=:producto AND (estado_pedido='abierto' OR estado_pedido='vencido') ");
		$b_r->bindParam(':producto', $producto);
		$b_r->execute();

		while ( $r_r = $b_r->fetchObject() ) $cantidad+= $r_r->cantidad;

		if ( $inventario-$cantidad < 0) $inventario = $cantidad+1;

		return $inventario;
	}

	/////////////////////////////////////////////////////////////////////
	/* Funcion para confirmar que la persona este logueada */
	/////////////////////////////////////////////////////////////////////
	function registrado(){
		if(!isset($_SESSION['id'])){
			$_SESSION['error'][] = "Debe iniciar session";
			header('location: '._BASE_URL.'/index.php');
		}else{
			$con = conecta();

			$id = desencriptar( limpiar( $_SESSION['id'] ) );

			$b = $con->prepare("SELECT COUNT(id) FROM aio_personal WHERE id_usuario=:id");
			$b->bindParam(':id',$id);
			$b->execute();
			
			if ( $b->fetchColumn() == 0 ){
				$_SESSION['error'][] = "Debe iniciar session";		
				header('location: '._BASE_URL.'/index.php');
			}
		}
	}

	/////////////////////////////////////////////////////////////////////
	/* Funcion para confirmar el nivel del usuario logueado */
	/////////////////////////////////////////////////////////////////////
	function nivel($comparado){
		$id 		= desencriptar( limpiar( $_SESSION['id'] ) );
		$$comparado = limpiar($comparado);
		$con = conecta();

		$b = $con->prepare("SELECT nivel FROM aio_personal WHERE id_usuario=:id");
		$b->bindParam(':id',$id);
		$b->execute();
		$b->bindColumn('nivel',$nivel);
		$b->fetch();

		if( $nivel != $comparado){			
			session_destroy();
			$_SESSION['error'][] = "Debe iniciar session";
			header('location: '._BASE_URL.'/index.php');		
		}
	}

	/////////////////////////////////////////////////////////////////////
	/* Funcion para confirmar el nivel del usuario logueado */
	/////////////////////////////////////////////////////////////////////
	function user_nivel($nivel,$con){
		$id = desencriptar( limpiar( $_SESSION['id'] ) );

		$b = $con->prepare("SELECT nivel FROM aio_personal WHERE id_usuario=:id");
		$b->bindParam(':id',$id);
		$b->execute();
		$r = $b->fetchObject();

		if ( $r->nivel == $nivel) $regresar = true;
		else $regresar = false;

		return $regresar;
	}
	/////////////////////////////////////////////////////////////////////
	/* Funcion para obtener el nombre del personal */
	/////////////////////////////////////////////////////////////////////	
	function nombre($id){
		$id = limpiar($id);
		$con = conecta();

		$b = $con->prepare("SELECT nombre,apellido FROM aio_personal WHERE id_usuario=:id");
		$b->bindParam(':id',$id);
		$b->execute();
		$b->bindColumn('nombre',$nombre);
		$b->bindColumn('apellido',$apellido);
		$b->fetch();

		return array($apellido,$nombre);
	}

	/////////////////////////////////////////////////////////////////////
	/* Funcion para imprimir el nombre del personal */
	/////////////////////////////////////////////////////////////////////	
	function nombre_personal($id){
		$id = limpiar($id);
		$con = conecta();

		$b = $con->prepare("SELECT nombre,apellido FROM aio_personal WHERE id_usuario=:id");
		$b->bindParam(':id',$id);
		$b->execute();
		$b->bindColumn('nombre',$nombre);
		$b->bindColumn('apellido',$apellido);
		$b->fetch();
		$nombre = $nombre.' '.$apellido;
		return $nombre;
	}	


	/////////////////////////////////////////////////////////////////////
	/* Funcion para imprimir info personal */
	/////////////////////////////////////////////////////////////////////	
	function info_personal($id){
		$id  = limpiar($id);
		$con = conecta();

		$b = $con->prepare("SELECT * FROM aio_personal WHERE id_usuario=:id");
		$b->bindParam(':id',$id);
		$b->execute();	
		return $b->fetchObject();
	}	

	///////////////////////////////////////////////////////////////////////////
	/* Funcion para obtener el nombre de la persona que atendio el pedido */
	///////////////////////////////////////////////////////////////////////////
	function ATN(){
		$id = desencriptar(  limpiar($_SESSION['id']) );
		$con = conecta();

		$b = $con->prepare("SELECT nombre,apellido FROM aio_personal WHERE id_usuario=:id");
		$b->bindParam(':id',$id);
		$b->execute();
		$b->bindColumn('nombre',$nombre);
		$b->bindColumn('apellido',$apellido);
		$b->fetch();
		$nombre = $nombre.' '.$apellido;
		return $nombre;
	}


	/////////////////////////////////////////////////////////////////////
	/* Funcion para obtener el apellido del personal logueado */
	/////////////////////////////////////////////////////////////////////
	function apellido($id){
		$id = desencriptar( limpiar($id) );
		$con = conecta();

		$b = $con->prepare("SELECT apellido FROM aio_personal WHERE id_usuario=:id");
		$b->bindParam(':id',$id);
		$b->execute();
		$b->bindColumn('apellido',$apellido);
		$b->fetch();

		return $apellido;
	}

	/////////////////////////////////////////////////////////////////////
	/* Funcion para obtener sucursal del usuario logueado */
	/////////////////////////////////////////////////////////////////////
	function sucursal(){
		$id = desencriptar( limpiar($_SESSION['id']) );
		$con = conecta();

		$b = $con->prepare("SELECT sucursal FROM aio_personal WHERE id_usuario=:id");
		$b->bindParam(':id',$id);
		$b->execute();
		$b->bindColumn('sucursal',$sucursal);
		$b->fetch();

		$b = $con->prepare("SELECT * FROM aio_sucursal WHERE id_sucursal=:id");
		$b->bindParam(':id',$sucursal);
		$b->execute();

		return $b->fetchObject();
	}


	/////////////////////////////////////////////////////////////////////
	/* Funcion para obtener el nombre de un cliente con su id */
	/////////////////////////////////////////////////////////////////////
	function nombre_cliente($id){
		$id = limpiar($id);
		$con = conecta();

		$b = $con->prepare("SELECT cliente FROM aio_cliente WHERE id_cliente=:id");
		$b->bindParam(':id',$id);
		$b->execute();
		$r = $b->fetchObject();
		
		return $r->cliente;
	}


	/////////////////////////////////////////////////////////////////////////
	/* Funcion para obtener toda la informacion de un cliente con su id */
	////////////////////////////////////////////////////////////////////////
	function info_cliente($id){
		$id  = limpiar($id);
		$con = conecta();

		$b = $con->prepare("SELECT * FROM aio_cliente WHERE id_cliente=:id");
		$b->bindParam(':id',$id);
		$b->execute();	
		return $b->fetchObject();
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////
	/* Funcion para obtener toda la informacion de una sucursal con la id de la sucursal  */
	////////////////////////////////////////////////////////////////////////////////////////////////
	function info_sucursal($id){
		$id  = limpiar($id);
		$con = conecta();

		$b = $con->prepare("SELECT * FROM aio_sucursal WHERE id_sucursal=:id");
		$b->bindParam(':id',$id);
		$b->execute();

		return $b->fetchObject();
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////
	/* Funcion para obtener toda la informacion de una sucursal con la id del usuario logueado  */
	/////////////////////////////////////////////////////////////////////////////////////////////////
	function sucursal_usuario(){		
		$usuario = desencriptar( limpiar($_SESSION['id']) );
		$con = conecta();
		
		$b = $con->prepare("SELECT sucursal FROM aio_personal WHERE id_usuario=:id");
		$b->bindParam(':id',$usuario);
		$b->execute();
		$r = $b->fetchObject();

		$b = $con->prepare("SELECT * FROM aio_sucursal WHERE id_sucursal=:id");
		$b->bindParam(':id',$r->sucursal );
		$b->execute();

		return $b->fetchObject();
	}	

	/////////////////////////////////////////////////////////////////////
	/* Funcion para obtener el nombre de un cliente con su id */
	/////////////////////////////////////////////////////////////////////
	function nombre_estado($id){
		$id  = limpiar($id);
		$con = conecta();

		$b = $con->prepare("SELECT nombre FROM aio_estados WHERE id=:id");
		$b->bindParam(':id',$id);
		$b->execute();
		$b->bindColumn('nombre',$nombre_estado);
		$b->fetch();

		return $nombre_estado;
	}

	function nombre_municipio($id,$estado){
		$id 	= limpiar($id);
		$estado = limpiar($estado);
		$con = conecta();
		
		$b = $con->prepare("SELECT nombre FROM aio_municipios WHERE clave=:id AND estado_id=:estado");
		$b->bindParam(':id',$id);
		$b->bindParam(':estado',$estado);
		$b->execute();
		$b->bindColumn('nombre',$nombre_municipio);
		$b->fetch();

		return $nombre_municipio;
	}
	

	/////////////////////////////////////////////////////////////////////
	/* Funcion dias habiles */
	/////////////////////////////////////////////////////////////////////	
	
	function contardias($fecha_inicial,$fecha_final){

		//Fecha incial
		$ini = explode(' ', $fecha_inicial); //Separar de la fecha de inicio la hora y el mes
		list($year,$mes,$dia) = explode("-",$ini[0]); //Listar año, mes y dia
		list($hor,$min,$seg)  = explode(":",$ini[1]); //Listar hora, minuto y segundo
		$fecha1 = mktime($hor,$min,$seg,$mes,$dia,$year); //Mktime completo Fecha de inicio

		//Fecha Final
		$fin = explode(' ', $fecha_final); //Separar de la fecha final la hora y el mes
		list($yearf,$mesf,$diaf) = explode("-",$fin[0]); //Listar año, mes y dia
		list($horf,$minf,$segf)  = explode(":",$fin[1]); //Listar hora, minuto y segundo
		$fecha2 = mktime($horf,$minf,$segf,$mesf,$diaf,$yearf); //Mktime completo Fecha final

		$diferencia = $fecha2-$fecha1; //Diferencia entre fecha de inicio y final

		$newArray['horas'] = (int)($diferencia/(60*60)); //Diferencia de horas
		$newArray['dias']  = (int)($diferencia/(60*60*24)); //Diferencia en dias		
				
		$r = 1;
		$fecha1 = mktime(0,0,0,$mes,$dia,$year); //Mktime con el dia
		$fecha2 = mktime(0,0,0,$mesf,$diaf,$yearf); //Mktime con el dia

		if ( $fecha_inicial > $fecha_final ) {
			while($fecha1 != $fecha2){
				$fecha2 = mktime(0,0,0, $mesf , $diaf+$r, $yearf);
				$newArray['mktime'][] = $fecha2;
				$r++;
			}
		}else{			
			while($fecha1 != $fecha2){
				$fecha1 = mktime(0, 0, 0, $mes , $dia+$r, $year);
				$newArray['mktime'][] = $fecha1;
				$r++;
			}
		}

		return $newArray;
	}
	
	function diashabiles( $arreglo,$contar_domingos){
	
		$j 	  = count($arreglo['mktime']);
		$dia_ = 0;

		for ($i=0;$i<=$j;$i++){
			$dia = $arreglo['mktime'][$i];
			$fecha = getdate($dia);
			if ( $fecha["wday"] == 0 && $contar_domingos == 0 ) $dia_+=24;
		}

		$arreglo['horas']    = (int) (abs($arreglo['horas']) - $dia_);
		$arreglo['duracion'] = (int) ($arreglo['horas']/24);
		$arreglo['domingos'] = $dia_;

		if ( $arreglo['dias'] > 0 ) {
			$arreglo['vencido']  = 0;

		}		

		elseif ( $arreglo['dias'] < 0 ){
			$arreglo['vencido'] = 1;									
		}
		
		elseif ( $arreglo['dias'] == 0 ) {
			
			$arreglo['vencido']  = 1;
			$arreglo['duracion'] = 0;

		}	

		return $arreglo;

	}


	/////////////////////////////////////////////////////////////////////
	/* Funcion contar dias */
	/////////////////////////////////////////////////////////////////////	
	
	function contar_dias_simple($fecha_inicial, $fecha_final){

		//Fecha incial
		$ini = explode(' ', $fecha_inicial); //Separar de la fecha de inicio la hora y el mes
		list($year,$mes,$dia) = explode("-",$ini[0]); //Listar año, mes y dia		
		$fecha1 = mktime(0,0,0,$mes,$dia,$year); //Mktime completo Fecha de inicio

		//Fecha Final
		$fin = explode(' ', $fecha_final); //Separar de la fecha final la hora y el mes
		list($yearf,$mesf,$diaf) = explode("-",$fin[0]); //Listar año, mes y dia		
		$fecha2 = mktime(0,0,0,$mesf,$diaf,$yearf); //Mktime completo Fecha final

		$diferencia = $fecha2 - $fecha1; //Diferencia entre fecha de inicio y final

		$newArray['horas'] = (int)( $diferencia/(60*60) ); //Diferencia de horas
		$newArray['dias']  = (int)( $diferencia/(60*60*24) ); //Diferencia en dias			

		return $newArray;
	}


	/////////////////////////////////////////////////////////////////////
	/* Funcion conectar base de datos*/
	/////////////////////////////////////////////////////////////////////
	function conecta(){
		try{
			$con = new PDO( _AIO_DSN,_AIO_USER,_AIO_PASS,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8") );
			$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return($con);
		}catch( PDOException $e ){
			$_SESSION['error'][] = "#4040 Fallo la conexion avise a los administradores: " . $e->getMessage();
			exit();
		}
	}

	/////////////////////////////////////////////////////////////////////
	/* Funcion conectar base de datos externa*/
	/////////////////////////////////////////////////////////////////////
	function conecta_extrangero(){
		try{
			$con = new PDO( _FAN_DSN,_FAN_USER,_FAN_PASS,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8") );
			$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return($con);
		}catch( PDOException $e ){
			$_SESSION['error'][] = "#4040 Fallo la conexion avise a los administradores: " . $e->getMessage();
			exit();
		}
	}


	/////////////////////////////////////////////////////////////////////
	/* Funcion mostrar errores */
	/////////////////////////////////////////////////////////////////////	
	
	function mostrar_errores(){
		///Avisos de error o de exito generados con sesiones
		if(isset($_SESSION['error'])){
			echo '<div class="grid_24">';
			foreach ($_SESSION['error'] as $error)
				echo '<div class="notice warning"><p><b>Error: </b>'.$error.'</p></div>';
			unset($_SESSION['error']);
			echo '</div>';
		}
			
		if (isset($_SESSION['bien'])){
			echo '<div class="grid_24">';
			foreach ($_SESSION['bien'] as $bien)
				echo '<div class="notice success"><p><b>'.$bien.'</b></p></div>';
			unset($_SESSION['bien']);
			echo '</div>';
		}
	}
	
	

	/////////////////////////////////////////////////////////////////////
	/* Funcion Obtener URL*/
	/////////////////////////////////////////////////////////////////////

	function get_url() {
		$parametros = array();
		$url = parse_url($_SERVER['REQUEST_URI']);
		foreach(explode("/", $url['path']) as $p)
			if ($p!='') $parametros[] = limpiar( $p );

		return $parametros;
	}

	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//funcion para limpiar
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	function limpiar($var){
		if ( !is_array($var)) {
			$var = trim($var);/* Quita los espacios finales */
			$var = htmlentities($var, ENT_QUOTES,"UTF-8");  /* Eliminamos codigo html de */
			$var = trim($var);				/* Quita los espacios finales */
		}

		return $var;
	}

	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//funcion para imprimir html limpio
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	function echo_limpiar($var){		
		$var = stripcslashes( html_entity_decode($var) );
		return $var;
	}


	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//funcion para limpiar de acentos de las palabras
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	function acentos($text,$si){
		$text = htmlentities($text, ENT_QUOTES, 'UTF-8');
		$text = strtolower($text);
		if($si=="si") 
			$patron = array(
				// Espacios, puntos y comas por guion
				'/[\.,]+/' => '-',
		 
				// Vocales
				'/&agrave;/' => 'a',
				'/&egrave;/' => 'e',
				'/&igrave;/' => 'i',
				'/&ograve;/' => 'o',
				'/&ugrave;/' => 'u',
		 
				'/&aacute;/' => 'a',
				'/&eacute;/' => 'e',
				'/&iacute;/' => 'i',
				'/&oacute;/' => 'o',
				'/&uacute;/' => 'u',
		 
				'/&acirc;/' => 'a',
				'/&ecirc;/' => 'e',
				'/&icirc;/' => 'i',
				'/&ocirc;/' => 'o',
				'/&ucirc;/' => 'u',
		 
				'/&atilde;/' => 'a',
				'/&etilde;/' => 'e',
				'/&itilde;/' => 'i',
				'/&otilde;/' => 'o',
				'/&utilde;/' => 'u',
		 
				'/&auml;/' => 'a',
				'/&euml;/' => 'e',
				'/&iuml;/' => 'i',
				'/&ouml;/' => 'o',
				'/&uuml;/' => 'u',
		 
				'/&auml;/' => 'a',
				'/&euml;/' => 'e',
				'/&iuml;/' => 'i',
				'/&ouml;/' => 'o',
				'/&uuml;/' => 'u',
		 
				// Otras letras y caracteres especiales
				'/&aring;/' => 'a'			
		 
				// Agregar aqui mas caracteres si es necesario
		 
			);
		else
			$patron = array(
				// Espacios, puntos y comas por guion
				'/[\.,]+/' => '-',
		 
				// Vocales
				'/&agrave;/' => 'a',
				'/&egrave;/' => 'e',
				'/&igrave;/' => 'i',
				'/&ograve;/' => 'o',
				'/&ugrave;/' => 'u',
		 
				'/&aacute;/' => 'a',
				'/&eacute;/' => 'e',
				'/&iacute;/' => 'i',
				'/&oacute;/' => 'o',
				'/&uacute;/' => 'u',
		 
				'/&acirc;/' => 'a',
				'/&ecirc;/' => 'e',
				'/&icirc;/' => 'i',
				'/&ocirc;/' => 'o',
				'/&ucirc;/' => 'u',
		 
				'/&atilde;/' => 'a',
				'/&etilde;/' => 'e',
				'/&itilde;/' => 'i',
				'/&otilde;/' => 'o',
				'/&utilde;/' => 'u',
		 
				'/&auml;/' => 'a',
				'/&euml;/' => 'e',
				'/&iuml;/' => 'i',
				'/&ouml;/' => 'o',
				'/&uuml;/' => 'u',
		 
				'/&auml;/' => 'a',
				'/&euml;/' => 'e',
				'/&iuml;/' => 'i',
				'/&ouml;/' => 'o',
				'/&uuml;/' => 'u',
		 
				// Otras letras y caracteres especiales
				'/&aring;/' => 'a',
				'/&ntilde;/' => 'n',
		 
				// Agregar aqui mas caracteres si es necesario
		 
			);
	 
		$text = preg_replace(array_keys($patron),array_values($patron),$text);
		return $text;
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	


	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	///Funcion para el cambio de tamaño de cualquier tipo de imagen y transparencias
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	function tamano($nombre,$archivo,$newwidth,$newheight,$path_images){	
		
		//the image -> variables
		$file_name = $archivo['name'];
		$file_type = $archivo['type'];
		$file_size = $archivo['size'];
		$file_tmp  = $archivo['tmp_name'];	

		//check the file's extension
		$ext = strrchr($file_name,'.');
		$ext = strtolower($ext); 
		
		$getExt = explode('.', $file_name);
		$file_ext = $getExt[count($getExt)-1];	
		
		//keep image type	 
		if($file_size){
			
			//Dependiendo el tipo de imagen
			if($file_type == "image/pjpeg" || $file_type == "image/jpeg")	$new_img = imagecreatefromjpeg($file_tmp);
			elseif($file_type == "image/gif") 								$new_img = imagecreatefromgif($file_tmp);
			elseif ($file_type == "image/png") 								$new_img = imagecreatefrompng($file_tmp); 
		 		
			//list the width and height and keep the height ratio.
			list($width, $height) = getimagesize($file_tmp);	
		
			if ($width!=$newwidth && $height!=$newheight){
				//function for resize image.
				$resized_img = imagecreatetruecolor($newwidth,$newheight);
				
				//Si la imagen es png o gif se usa eso para respetar la transparencia
				if($file_type == "image/png" || $file_type=="image/gif"){
		  			imagealphablending($resized_img, false);
		  			imagesavealpha($resized_img,true);
		  			$transparent = imagecolorallocatealpha($resized_img, 255, 255, 255, 127);
		  			imagefilledrectangle($resized_img, 0, 0, $newwidth, $newheight, $transparent);
		 		}		
				
				//the resizing is going on here!
				imagecopyresampled($resized_img, $new_img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);		
				
				//Image paths
				$imagen  = $nombre.".".$file_ext;							
				$dir_img = $path_images.$imagen;
				
				//finally, save the image
				if($file_type == "image/pjpeg" || $file_type == "image/jpeg")	imagejpeg($resized_img,$dir_img);	
				elseif($file_type == "image/gif") 								imagegif($resized_img,$dir_img);
				elseif ($file_type == "image/png") 								imagepng($resized_img,$dir_img);
		
				//Destruir la imagen temporal
				imagedestroy($resized_img);
			}else{
				//Image paths
				$imagen  = $nombre.".".$file_ext;							
				$dir_img = $path_images.$imagen;
				copy($file_tmp, $dir_img);
			}
			
			//Regresa el nombre de la imagen
			return $imagen;		
		}
			
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	///Funcion para crop de imagenes
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//$nombre      = El nombre que tendra la imagen como resultado del crop
	//$archivo     = La direccion hacia el archivo que se modificara
	//$newW        = El ancho de la imagen como resultado del crop
	//$newH        = La altura de la imagen como resultado del crop
	//$x           = Localizacion del area en x para el crop
	//$y           = Localizacion del area en y para el crop
	//$w           = Ancho de la imagen seleccionada para el crop
	//$h           = Altura de la imagen seleccionada para el crop
	//$path_images = Direccion donde se guardara el resultado del crop 
	 
	function crop($nombre,$archivo,$newW,$newH,$x,$y,$w,$h,$path_images){
		//Direccion exacta de la imagen
		$archivo = $path_images.$archivo;
		
		//Tipo de imagen
		$file_type = exif_imagetype($archivo);
		
		//Dependiendo el tipo de imagen
		if( $file_type == 2 ){
			$new_img = imagecreatefromjpeg($archivo);
			$file_ext = '.jpg';
		}elseif( $file_type == 1 ){
			$new_img = imagecreatefromgif($archivo);
			$file_ext = '.gif';
		}elseif ( $file_type == 3 ){
			$new_img = imagecreatefrompng($archivo);
			$file_ext = '.png';
		}
		
		//Se crea el contenedor del tamaño deseado
		$resized_img = imagecreatetruecolor( $newW, $newH );
				
		//Si la imagen es png o gif se usa eso para respetar la transparencia
		if( $file_type == 3 || $file_type== 1 ){
			imagealphablending($resized_img, false);
			imagesavealpha($resized_img,true);
		  	$transparent = imagecolorallocatealpha($resized_img, 255, 255, 255, 127);
		  	imagefilledrectangle($resized_img, 0, 0, $newW, $newH, $transparent);
		 }		
				
		//the resizing is going on here!
		imagecopyresampled($resized_img,$new_img,0,0,$x,$y,$newW,$newH,$w,$h);	
				
		//Image paths	
		$imagen  = $nombre.".".$file_ext;							
		$dir_img = $path_images.$imagen;
				
		//finally, save the image
		if( $file_type == 2 )	imagejpeg($resized_img,$dir_img);	
		elseif( $file_type == 1 ) 								imagegif($resized_img,$dir_img);
		elseif ( $file_type == 3 ) 								imagepng($resized_img,$dir_img);
			
		//Destruir la imagen temporal
		imagedestroy($resized_img);

		//Regresa el nombre de la imagen
		return $imagen;	
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	///Funcion para el cambio de tamaño de imagen web
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	function tamano_img_web($nombre,$archivo,$newwidth,$newheight,$path_images){
		 		
		//list the width and height and keep the height ratio.
		list($width, $height) = getimagesize($archivo);

		//Resample
		$image_p = imagecreatetruecolor($newwidth, $newheight);
		$image = imagecreatefromjpeg($archivo);	
				
		//the resizing is going on here!
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
			
		//Image paths
		$nombre_img = $nombre.".jpg";							
		$direc_imagen = $path_images.$nombre_img;
			
		//finally, save the image					
		imagejpeg($image_p,$direc_imagen,100);		
		imagedestroy($image_p);
			
		return $nombre_img;						            					  		
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
	///Dar formato de dinero
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	function dinero($number) { 
		return number_format((float) $number, 2, '.', ','); 
	}

	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//funcion escribir fecha
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	function fecha_nombre($fecha){
		$fecha = explode('-', $fecha);

		$mes = [
			'01' => 'Enero',
			'02' => 'Febrero',
			'03' => 'Marzo',
			'04' => 'Abril',
			'05' => 'Mayo',
			'06' => 'Junio',
			'07' => 'Julio',
			'08' => 'Agosto',
			'09' => 'Septiembre',
			'10' => 'Octubre',
			'11' => 'Noviembre',
			'12' => 'Diciembre'
		];
		return $fecha[0].' de '.$mes[$fecha[1]].' del '.$fecha[2];
	}

	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//funcion letra rand
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	function letra_rand($i){
		$characters = array(
		"A","B","C","D","E","F","G","H","I","J","K","L","M",
		"N","O","P","Q","R","S","T","U","V","W","X","Y","Z");

		//make an "empty container" or array for our keys
		$keys = array();

		//first count of $keys is empty so "1", remaining count is 1-6 = total 7 times
		while(count($keys) < $i) {
		    //"0" because we use this to FIND ARRAY KEYS which has a 0 value
		    //"-1" because were only concerned of number of keys which is 32 not 33
		    //count($characters) = 33
		    $x = mt_rand(0, count($characters)-1);
		    if(!in_array($x, $keys)) {
		       $keys[] = $x;
		    }
		}

		foreach($keys as $key){
		   $random_chars .= $characters[$key];
		}

		return $random_chars;
	}

	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//funcion para encriptar
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	function encriptar($string) {
		$key = _CLAVE;
		$result = '';

		for($i=0; $i<strlen($string); $i++){
			$char = substr($string, $i, 1);
			$keychar = substr($key, ($i % strlen($key))-1, 1);
			$char = chr(ord($char)+ord($keychar));
			$result.=$char;
		}

		return base64_encode($result);
	}


	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//funcion para desencriptar
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
	function desencriptar($string){
		$key = _CLAVE;
		$result = '';
		$string = base64_decode($string);

		for($i=0; $i<strlen($string); $i++) {
			$char = substr($string, $i, 1);
			$keychar = substr($key, ($i % strlen($key))-1, 1);
			$char = chr(ord($char)-ord($keychar));
			$result.=$char;
		}

		return $result;
	}

	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//funcion clean number
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	function cleanNumber($value)
	{
		if (is_numeric($value)) {
			return $value;
		} else {
			$v = str_replace('$', '', $value);
			$v = str_replace(',', '', $v);

			return doubleval($v);
		}
	}
