<page orientation="portrait" format="LETTER" backleft="7mm" backright="8mm" backbottom="14mm">

	<page_footer>
		<table style="width:100%;">
			<tr>
				<td style="width:20%">
					<img style="width:90%;" src="../assets/img/logopdf.jpg">
				</td>
			</tr>
		</table>
		<table style="width:100%;">
			<tr>
				<td style="text-align:left; width: 50%"><b>No:</b> <?php echo str_pad($id_requisicion, 4, '0', STR_PAD_LEFT); ?></td>
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
			<td style="width:35%; vertical-align:middle;"><img style="width:90%;" src="../assets/img/logopdf.jpg"></td>
			<td style="width:35%; font-size:30pt; font-weight:bold; vertical-align:middle;">Requisicion</td>
			<td style="width:30%; font-size:25pt; font-weight:bold; text-align:right;">No. <?php echo str_pad($id_requisicion, 4, '0', STR_PAD_LEFT); ?></td>
		</tr>
	</table>
	<table>
		<tr>
			<td class="left" style="width:50%; font-size:11pt; font-weight:bold; vertical-align:middle;">
				Con autorizacion: <?php $auth = info_personal( $requi->autorizo); echo "$auth->nombre $auth->apellido"; ?>
			</td>
			<td class="left" style="width:25%; font-size:11pt; font-weight:bold; vertical-align:middle;">
				<b><?php $sucursal = info_sucursal($requi->id_sucursal ); echo  $sucursal->nombre;?></b>
			</td>
			<td class="right" style="width:25%; font-size:11pt; font-weight:bold; vertical-align:middle;">
				<b><?php echo fecha_espanol( $requi->fecha ); ?></b>
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

	<!-- =============================== Información de la requisicoin ========================================= -->
	<table>
		<tr>
			<td class="left titulo" style="width:10%;">Solicita:  </td>
			<td class="left" style="width:40%;"><?php echo $requi->solicita; ?></td>
			<td class="left titulo" style="width:10%;">Prioridad:  </td>
			<td class="left" style="width:40%;"><?php echo $requi->prioridad; ?></td>
		</tr>
	</table>

	<br />
	
	<!-- =============================== Mensaje de la requisicion ========================================= -->
	<table>
		<tr>
			<td>
				<b>Por el motivo:</b><br />
				<?php echo base64_decode( $requi->motivo ); ?>
			</td>
		</tr>
	</table>

	<br />
	
	<!-- =============================== Contenido de la requisicion ========================================= -->

	<table class="borde" border="1" style="width:190mm;">
		<thead>
			<tr>
				<th class="titulo center" style="width:40%;">Material</th>
				<th class="titulo center" style="width:10%;">Cantidad</th>
				<th class="titulo center" style="width:10%;">P/U</th>
				<th class="titulo center" style="width:15%;">Subtotal</th>
				<th class="titulo center" style="width:10%">IVA</th>
				<th class="titulo center" style="width:15%;">Importe</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$subtotal = 0; //Por que php y sus mamadas ahi que declarar esta variable
				$b = $con->prepare("SELECT * FROM aio_requisicion_material WHERE id_requisicion=:id AND status = 'aceptado'"); 
				$b->bindParam(':id',$id_requisicion);
				$b->execute();
			?>
			<?php while( $r = $b->fetchObject() ) : ?>
				<tr>
					<td style="width:40%;">
						<b>Material:</b> <?php echo $r->material; ?><br />
						<b>Proveedor:</b> <?php echo $r->proveedor; ?>, <b>Dir.</b> <?php echo $r->direccion; ?>, <b>Tel.</b> <?php echo $r->telefono ?>
					</td>
					<td><?php echo $r->cantidad; ?> <?php echo $r->unidad; ?></td>
					<td>$<?php echo dinero($r->precio_unitario); ?></td>
					<td>$<?php echo dinero($r->precio_unitario*$r->cantidad); ?></td>
					<td>$<?php echo ($r->iva == 1.16) ? dinero($r->precio_unitario*0.16) : 0; ?></td>
					<td>$<?php echo dinero($r->precio_unitario*$r->cantidad*$r->iva); ?></td>
					<?php $subtotal+= $r->precio_unitario*$r->cantidad*$r->iva; ?>
				</tr>
			<?php endwhile; ?>
		</tbody>
		<tfoot>
			<tr>
				<td class="right sin_borde borde_right" colspan="5">Total</td>
				<td class="borde">$<?php echo dinero($subtotal); ?></td>
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