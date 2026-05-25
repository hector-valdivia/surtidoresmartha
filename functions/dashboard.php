<?php 

/******************************************************************
Find in this document, the functions necessary to operate the panel
controls as to whether the necessary consultations wings databases. 
File created by  WDCM http://www.wdcm.com.mx for UNIVERSAL CMS V0.1 
*******************************************************************/

///Dirreccion de la pagina (sin ella nada funciona)
//define('_BASE_URL','http://localhost/dash/'); 
define('_BASE_URL','http://www.drtrimmer.com.mx/dash/');
//Checar que la session se iniciara
if(!isset($_SESSION['id'])){
	$_SESSION['error'][] = "Debe iniciar session";		
	header('location: '._BASE_URL.'main.php');			
}

//Session para que el navegador de archivos funcione
$_SESSION['KCFINDER']['disabled'] = false;

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//funcion para limpiar
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function limpiar($user){
	$user = trim($user);
	$user = addslashes($user); 			/* Recibimos y eliminamos caracteres raros de $user*/
	$user = strip_tags($user);          /* Eliminamos tags de codigo en $user*/
	$user = htmlspecialchars($user);    /* Eliminamos codigo html de $user */
	$user = trim($user);
	return $user;
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	

function WEBSITE_STATUS(){
	$url = "http://csinergia.net";
	$pattern='|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i';
    if(preg_match($pattern, $url) > 0)$status = '<td class="right_end green" style="color:#9C0; font-weight:bold;">online</td>'; 
    else $status = '<td class="right_end green" style="color:red; font-weight:bold;">Down</td>';
	
	$con = conecta();
	$query_users_existing = $con->query("SELECT COUNT(id_unica) FROM usuarios WHERE id = '1'");
	$Result_users_existing = $query_users_existing->fetchColumn();
	
	$content='
	      <tr>
		  <td><span class="icon graph"></span> Estado</td>
		  <td class="right_end">'.$status.'</td>
		  </tr>
			
		  <tr>
		  <td><span class="icon users"></span>Usuarios</td>
		  <td class="right_end">'.$Result_users_existing.'</td>
		  </tr>
            
          <tr>
		  <td><span class="icon users"></span> Compras</td>
		  <td class="right_end"></td>
		  </tr>
            
          <tr>
		  <td><span class="icon graph"></span> Valor Activo</td>
		  <td class="right_end"></td>
		  </tr>';
	
	print $content;	
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
///Dar formato de dinero
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function formatMoney($number, $fractional=false) { 
    if ($fractional) { 
        $number = sprintf('%.2f', $number); 
    } 
    while (true) { 
        $replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1,$2', $number); 
        if ($replaced != $number) { 
            $number = $replaced; 
        } else { 
            break; 
        } 
    } 
    return $number; 
} 
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>
