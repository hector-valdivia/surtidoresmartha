<?php $logoPdf = __DIR__ . '/../assets/img/logopdf.jpg'; ?>
<page orientation="portrait" format="LETTER" backleft="7mm" backright="8mm" backbottom="14mm">

	<page_footer>
		<table style="width:100%;">
			<tr>
				<td style="width:20%">
						<img style="width:90%;" src="<?php echo $logoPdf; ?>">
				</td>
				<td sryle="text-align: left;">
					<?php echo nombre_personal($user_logueado->id_usuario); ?><br />
					<?php echo 'Sucursal '.$sucursal->nombre; ?>
				</td>
			</tr>
		</table>
		<table style="width:100%;">
			<tr>
				<td style="text-align:left; width: 50%"><b>No:</b> <?php echo $id_cotizacion; ?></td>
				<td style="text-align:right; width: 50%"><b>Pagina</b> [[page_cu]]/[[page_nb]]</td>
			</tr>
		</table>
	</page_footer>

	<style>
		table{
			vertical-align:middle;
			font-size:9pt;
			cellpadding:0;
			cellspacing:0;
			width:190mm;
			table-layout: fixed;
		}

		.sin_borde { border: 0px hidden; }

		tr td{
			padding:5px;
			overflow: hidden;
		}

		.borde_left { border-left: 1px solid black; }
		.borde_right { border-right: 1px solid black; }

		.borde{ 
			border:1px solid black;
			border-right: 1px solid black;
			border-collapse: collapse;
		}

		.borde tr td.cabezera{
			background-color: #000;
			color: #fff;
			font-weight:600;
			text-transform:uppercase;
		}

		p {
			margin-bottom: 2px;
			padding-bottom: 2px;
			-webkit-margin-before: 2px;
			-webkit-margin-after: 2px;
			-webkit-margin-start: 0px;
			-webkit-margin-end: 0px;
		}

		.titulo {
			background-color:black; 
			color:white; 
			padding: 5px;
			font-weight:600;
			text-transform:uppercase;
			font-weight:bold;
		}

		.center { text-align:center; }
		.left { text-align: left; }
		.right { text-align: right; }

		.g100{ width:100%; }

		.texto { text-align: justify; }
	</style>

	<!-- =========================================== Cabezera del pdf ========================================= -->
	<table>
		<tr>
				<td style="width:35%; vertical-align:middle;"><img style="width:90%;" src="<?php echo $logoPdf; ?>"></td>
			<td style="width:35%; font-size:14pt; font-weight:bold; vertical-align:middle;">Cotización</td>
			<td style="width:30%; font-size:14pt; font-weight:bold; text-align:center;">No. <?php echo $id_cotizacion; ?></td>
		</tr>
		<tr>
			<td class="right" colspan="3" style="font-size:14pt; font-weight:bold; vertical-align:middle;">
				<b><?php echo fecha_espanol( date('Y-m-d') ); ?></b>
			</td>
		</tr>
	</table>
	<table style="width:100%; width:190mm;">
		<tr>
			<?php
				$c = $con->prepare("SELECT COUNT(id) FROM aio_sucursal");
				$c->execute();
				$width = 100/$c->fetchColumn(); 
				$b = $con->prepare("SELECT * FROM aio_sucursal");
				$b->execute();
			?>
			<?php while ( $r = $b->fetchObject() ): ?>
				<td style="width: <?php echo $width; ?>%">
					<?php echo $r->nombre; ?><br/>
					Calle <?php echo $r->calle; ?> No. <?php echo $r->noext; ?> Col. <?php echo $r->colonia; ?><br/>
					C.P. <?php echo $r->cp; ?> Tel. <?php echo $r->telefono; ?>
				</td>
			<?php endwhile; ?>
		</tr>
	</table>

	<br />

	<!-- =============================== Información de la cotización ========================================= -->
	<table>
		<tr>
			<td class="right titulo" style="width:10%;">Para:</td>
			<td class="left" style="width:40%;"><?php echo $para; ?> con atencion de <?php echo $atencion; ?></td>
			<td class="right titulo" style="width:10%;">Asunto:</td>
			<td class="left" style="width:40%;"><?php echo $asunto_correo; ?></td>
		</tr>
	</table>

	<br />
	
	<!-- =============================== Mensaje de la cotizacion ========================================= -->
	<table>
		<tr>
			<td>
				En atención a su solicitud de cotización, ponemos a su estimable consideración 
				el siguiente presupuesto.
			</td>
		</tr>
	</table>

	<br />
	
	<!-- =============================== Contenido de la cotización ========================================= -->
	<?php $subtotal = 0; ?>
	<table class="borde" border="1" style="width:190mm;">
		<thead>
			<tr>
				<th class="titulo center" style="width:10%;">Cantidad</th>
				<th class="titulo center" style="width:20%;">Unidad</th>
				<th class="titulo center" style="width:40%;">Descripcion</th>
				<th class="titulo center" style="width:15%;">P/U</th>
				<th class="titulo center" style="width:15%;">Importe</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($row as $fila) : ?>
				<tr>
					<td><?php echo $fila['cantidad']; ?></td>
					<td><?php echo $fila['unidad']; ?></td>
					<td style="width:40%;"><?php echo $fila['descripcion']; ?></td>
					<td>$<?php echo $fila['pu']; ?></td>
					<td>$<?php echo $fila['costo']; ?></td>
					<?php $subtotal+= cleanNumber($fila['costo']); ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<td class="right sin_borde borde_right" colspan="4">Subtotal</td>
				<td class="borde">$<?php echo dinero($subtotal); ?></td>
			</tr>
			<tr>
				<td class="right sin_borde borde_right" colspan="4">IVA (16%)</td>
				<td class="borde">$<?php echo dinero($subtotal*0.16); ?></td>
			</tr>
			<tr>
				<td class="right sin_borde borde_right" colspan="4">Total Neto</td>
				<td class="borde">$<?php echo dinero($subtotal*1.16); ?></td>
			</tr>
		</tfoot>
	</table>

	<br />

	<!-- =============================== Notas ========================================= -->
	<?php if ( !empty($nota) ): ?>
		<table>
			<tr>
				<td class="left titulo" style="width:100%;">Notas:</td>
			</tr>
			<tr>
				<td><?php echo base64_decode($nota); ?></td>
			</tr>
		</table>
	<?php endif; ?>
</page>
