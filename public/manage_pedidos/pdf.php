<?php

date_default_timezone_set('America/Chihuahua');
session_start();

use Spipu\Html2Pdf\Html2Pdf;
require(__DIR__ . "/../../vendor/autoload.php");
include(__DIR__ . "/../../funciones.php");

registrado();

function fecha_pdf_valida($fecha)
{
	return !empty($fecha) && substr($fecha, 0, 10) !== '0000-00-00';
}

function fecha_pdf($fecha)
{
	return fecha_pdf_valida($fecha) ? substr($fecha, 0, 10) : 'Sin Fecha';
}

//Conexion de BD
$con = conecta();

///Obtenemos los GET's y convertimos a variables
if( $_GET ){
	$keys_post = array_keys($_GET);
	foreach ($keys_post as $key_post){
		$$key_post = $_GET[$key_post];
	}
}

//Limpiamos y sengriptamo $_GET[id]=id
$id = limpiar( desencriptar($id) );
if ( empty($id) ){
    header("location: table.php");
}

//id es igual al folio de la orden
$b = $con->prepare("SELECT * FROM aio_orden WHERE id=:id");
$b->bindParam(':id',$id);
$b->execute();
$orden = $b->fetchObject();

//Informacion del cliente de la orden
$cliente = info_cliente($orden->id_cliente);
//Informacion de la sucursal de la orden
$sucursal = info_sucursal($orden->sucursal);
$logoPdf = __DIR__ . '/../assets/img/logopdf.jpg';

//Cantidad de tiempo entre dos fechas, para asignar el tiempo que tiene para terminarse la orden de trabajo
if (fecha_pdf_valida($orden->fecha_orden) && fecha_pdf_valida($orden->fecha_deseada)) {
	$contar_dias = contar_dias_simple($orden->fecha_orden, $orden->fecha_deseada);
} else {
	$contar_dias = array('dias' => 'N/A');
}

//Diseño de la hoja del pdf
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$html = '
<page orientation="portrait" format="LETTER" backleft="7mm" backright="8mm" backbottom="14mm">

	<page_footer>
		<table style="width:100%;">
			<tr>
				<td style="text-align:left; width: 50%"><b>No:</b> '.$orden->folio.'</td>
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
		}

		tr td{
			cellpadding:0;
			cellspacing:0;
			padding:5px;
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

		.g100{ width:100%; }

		.texto { text-align: justify; }

	</style>

	<table>
		<tr>
			<td style="width:35%; vertical-align:middle;"><img style="width:90%;" src="'.$logoPdf.'"></td>
			<td style="width:35%; font-size:14pt; font-weight:bold; vertical-align:middle;">Solicitud-Orden de Trabajo</td>
			<td style="width:30%; font-size:14pt; font-weight:bold; text-align:center;">No. '.$orden->folio.'</td>
		</tr>

		<tr>
			<td class="center" style="font-size:14pt;">'.nombre_estado($sucursal->estado).','.nombre_municipio($sucursal->municipio,$sucursal->estado).'</td>
			<td>&nbsp;</td>			
			<td class="center" style="font-size:14pt;">'.$sucursal->nombre.'</td>
		</tr>
	</table>

	<br />

	<table class="borde" border="1">
		<tr>
			<td class="titulo center" style="width:70%;" colspan="2">Empresa</td>
			<td class="titulo center" style="width:30%;">Departamento</td>
		</tr>

		<tr>
			<td style="text-align:center; vertical-align:middle;" colspan="2">'.$cliente->cliente.'</td>
			<td style="text-align:center; vertical-align:middle;">'.$orden->departamento.'</td>
		</tr>

		<tr>
			<td class="titulo center">Fecha Orden</td>
			<td class="titulo center">Fecha Entrega</td>
			<td class="titulo center">Prioridad</td>
		</tr>

		<tr>
			<td style="text-align:center; vertical-align:middle;">'.fecha_pdf($orden->fecha_orden).'</td>
			<td style="text-align:center; vertical-align:middle;">'.fecha_pdf($orden->fecha_deseada).'</td>
			<td style="text-align:center; vertical-align:middle;">'.$orden->prioridad.'</td>
		</tr>
	</table>

	<br />

	<table class="borde" border="1">
		<tr>
			<td class="titulo center" style="width:25%;">Solicitante</td>
			<td class="titulo center" style="width:25%;">Planeacion</td>
			<td class="titulo center" style="width:25%;">Aprobo</td>
			<td class="titulo center" style="width:25%;">T.Permitido (Dias)</td>
		</tr>

		<tr>
			<td style="width:25%; text-align:center; vertical-align:center;">'.$orden->solicitante.'</td>
			<td style="width:25%; text-align:center; vertical-align:center;">'.nombre_personal($orden->planeador).'</td>
			<td style="width:25%; text-align:center; vertical-align:center;">'.nombre_personal($orden->aprobo).'</td>
			<td style="width:25%; text-align:center; vertical-align:center;">'.$contar_dias['dias'].'</td>
		</tr>
	</table>

	<br />

	<table class="borde" border="1">
		<tr><td class="titulo g100">Trabajo Solicitado</td></tr>
	</table>
		'.html_entity_decode($orden->trabajo_solicitado).'

	<br />

	<table class="borde" border="1">
		<tr><td class="titulo g100">Trabajo a Realizar</td></tr>
	</table>
		'.html_entity_decode($orden->trabajo_realizar).'

	<br />

	<table class="borde" border="1">
		<tr><td class="titulo g100">Diagnostico de la falla y observaciones</td></tr>
	</table>
	'.html_entity_decode($orden->diagnostico_observaciones).'
	
	<br />

</page>';

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////// Tabla de personal ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////Generar la tabla de personal que trabajo dentro de esta orden
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//Seleccionamos la informacion de la tabla de personal de la orden, agrupandolos para asi sumar los dias trabajados para genera una lista
$b = $con->prepare("SELECT 
    id_personal,
    categoria,
    dia,
    SUM(costo) AS costo,
    SUM(horas) AS horas 
    FROM aio_orden_personal 
    WHERE orden_id=:orden_id 
    GROUP BY id_personal,categoria,dia 
    ORDER BY id_personal,dia");
$b->bindParam(':orden_id', $orden->id);
$b->execute();

//Inicializar variables
$personal 	 = '';
$total_costo = 0;
$i 			 = 0;

while ( $r = $b->fetchObject() ){
	$info_personal = info_personal( $r->id_personal );
	$costo = $r->costo;
	$horas = $r->horas;

	if ( $horas != 0 ){
		$personal.= 
			'<tr>
				<td style="text-align:left;">'.nombre_personal($r->id_personal).'</td>
				<td class="center">'.$r->categoria .'</td>
				<td class="center">'.fecha_pdf($r->dia).'</td>
				<td class="center">'.$horas .'</td>		
				<td class="center">$'.$costo.'</td>
			</tr>';

		$total_costo+= $costo;
		$i++;
	}
}


if ( $i < 10 ) {
	for($o=1; $o<=10-$i; $o++){
		$personal.='
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>';
	}
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////// Tabla de material usado
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$b = $con->prepare("SELECT * FROM aio_orden_material WHERE orden_id=:orden_id");
$b->bindParam(':orden_id', $orden->id);
$b->execute();

$i=0;
$material = '';
$total_material = 0;
while ( $r = $b->fetchObject() ){
	$material.= '
		<tr>
			<td style="text-align:left;">'.$r->material.'</td>
			<td style="text-align:center;">'.$r->cantidad.' '.$r->unidad.'</td>
			<td style="text-align:center;">$'.$r->costo.'</td>
		</tr>';

	$i++;
	$total_material+= $r->costo;
}

if ( $i < 10 ) {
	for($o=1; $o<=10-$i; $o++){
		$material.='
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>';
	}
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////// Tabla de herramientas usadas
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$b = $con->prepare("SELECT * FROM aio_orden_herramienta WHERE orden_id=:orden_id");
$b->bindParam(':orden_id', $orden->id);
$b->execute();

$i=0;
$herramienta = '';
while ( $r = $b->fetchObject() ){
	$herramienta.= '
		<tr>
			<td style="text-align:left;">'.$r->herramienta.'</td>
			<td style="text-align:center;">'.$r->cantidad.'</td>
		</tr>';

	$i++;
}

if ( $i < 10 ) {
	for($o=1; $o<=10-$i; $o++){
		$herramienta.='
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>';
	}
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$costo_total_reparacion = $total_costo+$total_material;


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////// Tabla de personal, material usado y herramienta acomodado
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$html.= '
<page orientation="portrait" format="LETTER" backleft="7mm" backright="8mm">
	<page_footer>
		<table style="width:100%;">
			<tr>
				<td style="text-align:left; width: 50%"><b>Folio:</b> '.$orden->folio.'</td>
				<td style="text-align:right; width: 50%"><b>Pagina</b> [[page_cu]]/[[page_nb]]</td>
			</tr>
		</table>
	</page_footer>	

	<table class="borde" border="1">
		<tr>
			<td class="titulo center" style="width:48%;">Personal</td>
			<td class="titulo center" style="width:20%;">Categoria</td>
			<td class="titulo center" style="width:12%;">Fecha</td>
			<td class="titulo center" style="width:08%;">Horas</td>
			<td class="titulo center" style="width:12%;">Costo</td>
		</tr>

		'.$personal.'

		<tr>
			<td style="text-align:right; font-weight:bold;" colspan="4">Total</td>
			<td>$'.$total_costo.'</td>
		</tr>

	</table>

	<br/>

	<table class="borde" border="1">
		<tr>
			<td class="titulo center" style="width:60%;">Materiales Usados</td>
			<td class="titulo center" style="width:20%;">Cantidad</td>
			<td class="titulo center" style="width:20%;">Costo</td>
		</tr>

		'.$material.'

		<tr>
			<td style="text-align:right; font-weight:bold;" colspan="2">Total</td>
			<td>$'.$total_material.'</td>
		</tr>			
	</table>

	<br/>

	<table class="borde" border="1">
		<tr>
			<td class="titulo center" style="width:75%;">Costo Total de la Reparacion</td>
			<td class="center" style="width:25%;">$'.dinero($costo_total_reparacion).'</td>
		</tr>
	</table>

	<br/>

	<table class="borde" border="1">
		<tr>
			<td class="titulo center" style="width:80%;">Herramienta Utilizada</td>
			<td class="titulo center" style="width:20%;">Cantidad</td>
		</tr>

		'.$herramienta.'			
	</table>	
</page>';
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//echo $html;

try{
	$html2pdf = new HTML2PDF('P','LETTER','es',array('mL', 'mT', 'mR', 'mB'));
	$html2pdf->pdf->SetDisplayMode('fullpage');
	$html2pdf->WriteHTML($html);
	$html2pdf->setDefaultFont('helvetica');	
	$html2pdf->Output($id.'.pdf');
}catch(Exception $e) {
	//Falla generada por la creacion del pdf
	$_SESSION['error'] = $e->getMessage();
	header('location:table.php');
}
