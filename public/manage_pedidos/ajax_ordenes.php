<?php

//Funciones requeridas
require_once(__DIR__ . "/../../funciones.php");
require_once(__DIR__ . "/../../functions/ssp.class.php");

session_start();
//Get de sucursal de la session
$sucursal = sucursal();

// DB table to use
$table = 'aio_orden';
 
// Table's primary key
$primaryKey = 'id';

// indexes
$columns = array(
    array( 'db' => 'folio', 'dt' => 0 ),
    array( 'db' => 'id_cliente', 'dt' => 1, 'formatter' => function( $cliente, $row ){
        return nombre_cliente($cliente);
    }),
    array( 'db' => 'sucursal', 'dt' => 2, 'formatter' => function( $sucursal, $row ){
        $sucursal = info_sucursal($sucursal);
        return $sucursal->nombre;
    }),

    array( 'db' => 'fecha_orden', 'dt' => 3, 'formatter' => function( $fecha, $row ){
        return substr($fecha, 0,10);
    }),
    array( 'db' => 'fecha_inicio', 'dt' => 4, 'formatter' => function( $fecha, $row ){
        if ( empty($fecha) || is_null($fecha) ) $fecha = 'Sin Fecha';
        else $fecha = substr($fecha, 0,10);
        return $fecha;
    }),
    array( 'db' => 'fecha_deseada', 'dt' => 5, 'formatter' => function( $fecha, $row ){
        return substr($fecha, 0,10);
    }),

    array( 'db' => 'id', 'dt' => 6, 'formatter' => function( $orden_id, $row ) {
            $html = '<a href="pdf.php?id='.encriptar($orden_id).'"  target="_blank" class="view tip" title="Ver PDF">Ver PDF</a>';
            $html.= '<a href="info.php?id='.encriptar($orden_id).'" class="edit tip" title="Editar">Editar</a>';
            return $html;
        }
    )
);
 
// SQL server connection information
$sql_details = [
    'user' => _AIO_USER,
    'pass' => _AIO_PASS,
    'db'   => AIO_DB,
    'host' => AIO_HOST
];

$request = !empty($_POST) ? $_POST : $_GET;
$tipo = $request['tipo'] ?? 'abierto';
$tiposPermitidos = ['abierto', 'vencido', 'cancelado', 'terminado', 'todos'];

if ( !in_array($tipo, $tiposPermitidos, true) ) {
    $tipo = 'abierto';
}

//$where = 'AND sucursal='.$sucursal->id_sucursal;

$where = null;
if ( $tipo != 'todos' ) {
    $where = "AND estado_orden='".$tipo."'";
}

echo json_encode(
    SSP::simple( $request, $sql_details, $table, $primaryKey, $columns, $where )
);
