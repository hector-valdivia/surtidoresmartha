<?php

//Funciones requeridas
require_once('../funciones.php');

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

    array( 'db' => 'folio', 'dt' => 6, 'formatter' => function( $folio, $row ) {
            $html = '<a href="pdf.php?id='.encriptar($folio).'"  target="_blank" class="view tip" title="Ver PDF">Ver PDF</a>';
            $html.= '<a href="info.php?id='.encriptar($folio).'" class="edit tip" title="Editar">Editar</a>';
            return $html;
        }
    )
);
 
// SQL server connection information
$sql_details = array(
    'user' => _AIO_USER,
    'pass' => _AIO_PASS,
    'db'   => AIO_DB,
    'host' => AIO_HOST
);
 
require( '../functions/ssp.class.php' );

//$where = 'AND sucursal="'.$sucursal->id_sucursal.'"';

//if ( !empty($_POST['tipo']) && $_POST['tipo'] != 'todos' ) $where.= ' AND estado_orden="'.$_POST['tipo'].'"';

$where = 'AND estado_orden="'.$_POST['tipo'].'"';

echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns, $where )
);