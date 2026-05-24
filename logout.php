<?php
	include('funciones.php');
	session_start();
	session_destroy();
	header('location:'._BASE_URL.'/main');
?>