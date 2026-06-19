<?php
session_start();
include(__DIR__ . "/../../funciones.php");

$con = conecta();

/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simply to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

// DB table to use
$table = 'aio_requisicion';

// Table's primary key
$primaryKey = 'id';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes

$columns = array(
	array( 'db' => 'id', 'dt' => 0 ),
	array( 'db' => 'descripcion',  'dt' => 1 ),
	array( 'db' => 'fecha',   'dt' => 2 ),
	array( 'db' => 'id_sucursal', 'dt' => 3, 'formatter' => function( $id_sucursal, $row ) use ($con){
		$b = $con->prepare("SELECT nombre FROM aio_sucursal WHERE id_sucursal=:id LIMIT 1");
		$b->bindParam(':id',$id_sucursal);
		$b->execute();
		$sucursal = $b->fetchObject();
		return $sucursal->nombre;
	}),
	array( 'db' => 'solicita', 'dt' => 4 ),
	array( 'db' => 'autorizo', 'dt' => 5, 'formatter' => function( $autorizo, $row ) use ($con){
		$a = "En espera de autrizacion";
		if (!empty($autorizo)) {
			$b = $con->prepare("SELECT nombre, apellido FROM aio_personal WHERE id_usuario=:id LIMIT 1");
			$b->bindParam(':id',$autorizo);
			$b->execute();	
			$auth = $b->fetchObject();
			$a = $auth->nombre.' '.$auth->apellido;
		}

		return $a;
	}),
	array( 'db' => 'status',   'dt' => 6, 'formatter' => function( $status, $row ){
		switch ( $status ) {
			case 'cancelado':  $label = 'label-warning'; break;
			case 'aceptado':   $label = 'label-success'; break;
			case 'espera': 	    $label = 'label-info'; break;
			default: $label = 'label-default'; break;
		}
		return '<span class="label '.$label.'">'.$status.'</span>';
	}),
    array( 'db' => 'id', 'dt' => 7, 'formatter' => function( $id, $row ) use ($con){
		$b = $con->prepare("SELECT id, pdf FROM aio_requisicion WHERE id=$id ORDER BY fecha DESC");
		$b->execute();
		$r = $b->fetchObject();

		#Herramientas
		$html = '';
		if ($r->pdf == 'si'){
			$html.= '<a href="/manage_requisicion/print.php?id='.encriptar($id).'" target="_blank" class="btn-circle tip" title="PDF"><span class="glyphicon glyphicon-print"></span></a>';
		}
		$html.= '<a href="agregar.php?id='.encriptar($id).'" class="btn-circle tip" title="Editar"><span class="glyphicon glyphicon-edit"></span></a>';
		$html.= '<a href="#"  data-id="'.encriptar($id).'" class="btn-circle tip" title="Borrar"><span class="glyphicon glyphicon-trash"></span></a>';

        return $html;
	})
    
);

// SQL server connection information
$sql_details = array(
    'user' => _AIO_USER,
    'pass' => _AIO_PASS,
    'db'   => AIO_DB,
    'host' => AIO_HOST
);

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
require_once(__DIR__ . "/../../functions/ssp.class.php");

$where = null;
if ( !user_nivel($nivel=1, $con) ){
	$sucursal = sucursal();	
	$id_sucursal = preg_replace('/[^A-Za-z0-9_-]/', '', $sucursal->id_sucursal);
	$where = "AND id_sucursal='".$id_sucursal."'";
}

echo json_encode(SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns, $where ));
