<?php
	session_start();
	include(__DIR__ . "/../../funciones.php");
	registrado();

	$con = conecta();
	$id_cliente = isset($_GET['id']) ? limpiar(desencriptar($_GET['id'])) : '';

	$b = $con->prepare("SELECT id_usuario,nombre,apellido,nivel FROM aio_personal WHERE id_usuario=:id AND nivel<>0 LIMIT 1");
	$b->bindParam(':id',$id_cliente);
	$b->execute();
	$r = $b->fetchObject();

	if ( !$r ) {
		$_SESSION['error'][] = "El usuario no tiene acceso al sistema";
		header("location:table.php");
		exit;
	}
?>

<!DOCTYPE HTML>
<html lang="es">
<head>
	<?php include(__DIR__ . "/../../modules/header.php"); ?>
	<title>Cambiar contraseña <?php echo $r->nombre.' '.$r->apellido; ?></title>
</head>
<body>

<div id="wrap" class="container_24">

	<?php include(__DIR__ . "/../../modules/menu.php"); ?>

	<?php mostrar_errores(); ?>

	<div class="grid_24">
		<div class="box_top">
			<h2 class="icon lock">Cambiar contraseña de <i><?php echo $r->nombre.' '.$r->apellido; ?></i></h2>
		</div>

		<div class="box_content padding">
			<form action="enviar.php" name="editar_password" id="editar_password" class="validar" method="post">
				<div class="field">
					<label class="left">Nueva contraseña*</label>
					<input type="password" name="password" id="password_nuevo" autocomplete="off" class="medium validate[required,equals[password2_nuevo]] tip-stay" value="" title="Ingrese nueva contraseña" />
				</div>

				<div class="field">
					<label class="left">Confirmar contraseña*</label>
					<input type="password" name="password2" id="password2_nuevo" autocomplete="off" class="medium validate[required,equals[password_nuevo]] tip-stay" value="" title="Confirme nueva contraseña" />
				</div>

				<input type="text" name="id" value="<?php echo $id_cliente; ?>" readonly="true" class="small validate[required] text-input" style="display:none;" />
				<input type="text" name="hacer" value="password" readonly="true" class="small validate[required] text-input" style="display:none;" />
				<button>Actualizar contraseña</button>
			</form>
		</div>
	</div>

	<?php include(__DIR__ . "/../../modules/pie.php"); ?>

</div>

<?php include(__DIR__ . "/../../modules/js.php"); ?>

</body>
</html>
