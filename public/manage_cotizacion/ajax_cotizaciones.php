<?php

//Funciones requeridas
require_once(__DIR__ . "/../../funciones.php");
require_once(__DIR__ . "/../../functions/ssp.class.php");

session_start();
//Get de sucursal de la session
$sucursal = sucursal();

// DB table to use
$table = 'aio_cotizacion';
 
// Table's primary key
$primaryKey = 'id';

// indexes
$columns = array(
    array( 'db' => 'id_cotizacion', 'dt' => 0 ),
    array( 'db' => 'para', 'dt' => 1 ),
    array( 'db' => 'asunto',  'dt' => 2 ),
    array( 'db' => 'fecha',   'dt' => 3 ),
    array(
        'db' => 'hora',
        'dt' => 3,
        'formatter' => function( $hora, $row ) {
            return $row['fecha'].' '.$hora;
        }
    ),
    array(
        'db' => 'id_usuario_creo',     
        'dt' => 4,
        'formatter' => function( $id_usuario, $row ) {
            $con = conecta();
            $b = $con->prepare("SELECT CONCAT(nombre, ' ', apellido) as nombre_completo FROM aio_personal WHERE id_usuario=:id LIMIT 1");
            $b->bindParam(':id', $id_usuario);
            $b->execute();
            $r = $b->fetchObject();
            return $r->nombre_completo;
        }
    ),
    array(
        'db'        => 'id',
        'dt'        => 5,
        'formatter' => function( $id, $row ) {
            $html = '<a href="print.php?id='.encriptar($id).'" target="_blank" class="btn-circle tip" title="PDF"><span class="glyphicon glyphicon-print"></span></a>';
            $html.= '<a href="agregar.php?id='.encriptar($id).'" class="btn-circle tip" title="Editar"><span class="glyphicon glyphicon-edit"></span></a>';
			$html.= '<a href="#"  data-id="'.encriptar($id).'" class="btn-circle tip" title="Borrar"><span class="glyphicon glyphicon-trash"></span></a>';
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

$id_sucursal = preg_replace('/[^A-Za-z0-9_-]/', '', $sucursal->id_sucursal);
$where = "AND id_sucursal='".$id_sucursal."'";

echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns, $where )
);
