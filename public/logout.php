<?php
	include(__DIR__ . "/../funciones.php");
	session_start();
	session_destroy();
	header('location:'._BASE_URL.'/index.php');
?>
