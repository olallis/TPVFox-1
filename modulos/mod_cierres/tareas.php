<?php
/* Fichero de tareas a realizar.
 * 
 * 
 * Con el switch al final y variable $pulsado
 * 
 *  */


$pulsado = $_POST['pulsado'];
include_once ("./../../configuracion.php");

// Crealizamos conexion a la BD Datos
include_once ("./../mod_conexion/conexionBaseDatos.php");

// Incluimos funciones
include_once ("./funciones.php");

 switch ($pulsado) {
     
    case 'insertarCierre':
		$datosCierre = $_POST['datos_cierre'];
		$respuesta =  array();
		$insert = insertarCierre($BDTpv, $datosCierre);
		
		//insertar cierre de ivas?
		
		$respuesta = $insert;
		echo $respuesta['sqlInsert'];
		echo $respuesta['prueba1'];
		echo json_encode($respuesta);
    break;
    
    
}



 
 
?>
