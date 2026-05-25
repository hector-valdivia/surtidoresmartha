<?php $logoPdf = __DIR__ . '/../assets/img/logopdf.jpg'; ?>
<page orientation="portrait" format="LETTER" backleft="7mm" backright="8mm" backbottom="14mm">

	<page_footer>
		<table style="width:100%;">
			<tr>
				<td style="text-align:left; width: 50%"><b>Reporte de Entradas y Salidas:</b> <?php echo $empleado; ?></td>
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

		.borde{ 
			border:5px solid red;
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
			<td style="width:65%; font-size:20pt; font-weight:bold; vertical-align:middle;">Reporte de Entradas y Salidas</td>
		</tr>
		<tr>
			<td class="right" colspan="2" style="font-size:14pt; font-weight:bold; vertical-align:middle;">
				Fechas: <b><?php echo fecha_espanol( $desde ); ?></b> - <b><?php echo fecha_espanol( $hasta ); ?></b><br>
			</td>
		</tr>
	</table>
	<br />	
	<!-- =============================== Contenido de la cotización ========================================= -->
	<?php $subtotal = 0; ?>
	<table class="borde" border="1" style="width:190mm;">
		<thead>
			<tr>
				<th class="titulo center" style="width:15%;">Fecha</th>
				<th class="titulo center" style="width:15%;">Hora</th>
				<th class="titulo center" style="width:40%;">Personal</th>
				<th class="titulo center" style="width:20%;">Incidencia</th>
				<th class="titulo center" style="width:10%;">Sucursal</th>
			</tr>
		</thead>
		<tbody>
			<?php 
				if ( $empleado == 'todos' ) {
					if ( $sucursal == 'todas' ) $b = $ext->prepare("SELECT * FROM aio_asistencia WHERE fecha=:desde");
					else{
						$b = $ext->prepare("SELECT * FROM aio_asistencia WHERE sucursal=:sucursal WHERE fecha=:desde");
						$b->bindParam(':sucursal', $sucursal);
					}
				}else{
					if ( $sucursal == 'todas' ) $b = $ext->prepare("SELECT * FROM aio_asistencia WHERE id_personal=:id AND fecha=:desde");
					else{
						$b = $ext->prepare("SELECT * FROM aio_asistencia WHERE sucursal=:sucursal AND id_personal=:id AND fecha=:desde");
						$b->bindParam(':sucursal', $sucursal);
					}
					$b->bindParam(':id', $empleado);
				}

				$fecha1 = new DateTime($desde);
				$fecha2 = new DateTime($hasta);
				$diferencia = $fecha1->diff($fecha2);
			?>
			<?php for ( $i=0; $i <= $diferencia->format('%a'); $i++ ): ?>
				<?php
					$dia = $fecha1->format('Y-m-d');
					$b->bindParam(':desde', $dia);
					$b->execute();
				?>
				<?php if ( $b->rowCount() != 0 ): ?>
					<?php while ( $r = $b->fetchObject() ): ?>
						<tr>
							<td><?php echo $r->fecha; ?></td>
							<td><?php echo $r->hora; ?></td>
							<td><?php echo $r->nombre; ?></td>
							<td><?php echo ( $r->tipo == 'E' ) ? 'Entrada' :  'Salida'; ?></td>
							<td><?php $info_sucursal = info_sucursal($r->sucursal); echo $info_sucursal->nombre; ?></td>
						</tr>
					<?php endwhile; ?>
				<?php else: ?>
					<tr><td colspan="5" class="center">
						No hubo actividad este dia <?php echo fecha_es( $fecha1->format('Y-m-d') );  ?>
					</td></tr>
				<?php endif; ?>
				<?php $fecha1->modify('+1 day'); ?>	
			<?php endfor; ?>
		</tbody>
	</table>

	<br />
</page>
