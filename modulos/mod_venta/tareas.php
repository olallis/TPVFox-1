<?php
/* Fichero de tareas a realizar.
 * 
 * 
 * Con el switch al final y variable $pulsado
 * 
 *  */
/* ===============  REALIZAMOS CONEXIONES  ===============*/


$pulsado = $_POST['pulsado'];
use Mike42\Escpos\Printer;

include_once ("./../../configuracion.php");

// Crealizamos conexion a la BD Datos
include_once ("./../mod_conexion/conexionBaseDatos.php");

// Incluimos funciones
include_once ("./funciones.php");

include_once("clases/pedidosVentas.php");
$CcliPed=new PedidosVentas($BDTpv);
switch ($pulsado) {
    
    case 'buscarProductos':
		$busqueda = $_POST['valorCampo'];
		$campoAbuscar = $_POST['campo'];
		$id_input = $_POST['cajaInput'];
		$idcaja=$_POST['idcaja'];
		$deDonde = $_POST['dedonde']; // Obtenemos de donde viene
		$respuesta = BuscarProductos($id_input,$campoAbuscar, $idcaja, $busqueda,$BDTpv);
		if ($respuesta['Estado'] !='Correcto' ){
			// Al ser incorrecta entramos aquí.
			// Mostramos popUp tanto si encontro varios como si no encontro ninguno.
			if (!isset($respuesta)){
				$respuesta = array('datos'=>array());
			}
			$respuesta['listado']= htmlProductos($respuesta['datos'],$id_input,$campoAbuscar,$busqueda);
		}
		if ($respuesta['Estado'] === 'Correcto' && $deDonde === 'popup'){
			// Cambio estado para devolver que es listado.
			$respuesta['listado']= htmlProductos($respuesta['datos'],$id_input,$campoAbuscar,$busqueda);
			$respuesta['Estado'] = 'Listado';
		}
		
		
		echo json_encode($respuesta);  
		break;
		
		
		
	    case 'buscarClientes':
		// Abrimos modal de clientes
		$busqueda = $_POST['busqueda'];
		$dedonde = $_POST['dedonde'];
		$idcaja=$_POST['idcaja'];
		$tabla='clientes';
		$numPedido=$_POST['numPedido'];
		$idTienda=$_POST['idTienda'];
		$idUsuario=$_POST['idUsuario'];
		$estadoPedido=$_POST['estadoPedido'];
		
		$res = array( 'datos' => array());
		//funcion de buscar clientes
		//luego html mostrar modal 
			//$res = BusquedaClientes($busqueda);
		$res = BusquedaClientes($busqueda,$BDTpv,$tabla, $idcaja);
		if ($res['Nitems']===1){
			if ($numPedido>0){
				//Si el número de busquedas es uno quiere decir que la busqueda fue por id
			$modCliente=$CcliPed->ModClienteTemp($busqueda, $numPedido, $idTienda, $idUsuario, $estadoPedido);
			$respuesta['sql']=$modCliente;
			$respuesta['busqueda']=$busqueda;
			$respuesta['numPedido']=$numPedido;
			}else{
			$addCliente=$CcliPed->AddClienteTemp($busqueda, $idTienda, $idUsuario, $estadoPedido);
			$respuesta['numPedido']=$addCliente;
		
		}
			//~ $respuesta=htmlClientesCajas($res['datos']);
			$respuesta['nombre']=$res['datos'][0]['nombre'];
			
			
		}elseif($res['Nitems']>1){
			$respuesta = htmlClientes($busqueda,$dedonde, $idcaja, $res['datos']);
		
		}else{
		$respuesta = htmlClientes($busqueda,$dedonde, $idcaja, $res['datos']);
		
		}
		
		//~ echo $respuesta;
		echo json_encode($respuesta);
		break;
		
		
		
		case 'escribirCliente':
		// Cuando la busqueda viene a traves de  la ventana modal
		$id=$_POST['idcliente'];
		$tabla='clientes';
		$numPedido=$_POST['numPedido'];
		$idTienda=$_POST['idTienda'];
		$idUsuario=$_POST['idUsuario'];
		$estadoPedido=$_POST['estadoPedido'];
		if ($numPedido>0){
			$modCliente=$CcliPed->ModClienteTemp($id, $numPedido, $idTienda, $idUsuario, $estadoPedido);
			$respuesta['sql']=$modCliente;
			$respuesta['busqueda']=$id;
			$respuesta['numPedido']=$numPedido;
			}else{
			$addCliente=$CcliPed->AddClienteTemp($id, $idTienda, $idUsuario, $estadoPedido);
			$respuesta['numPedido']=$addCliente;
		}
		echo json_encode($respuesta);
		break;
		
		
		
		
			case 'HtmlLineaLinea':
		$respuesta = array();
		$product 					=$_POST['producto'];
		$num_item					=$_POST['num_item'];
		$CONF_campoPeso		=$_POST['CONF_campoPeso'];
		$res 	= htmlLineaTicket($product,$num_item,$CONF_campoPeso);
		$respuesta['html'] =$res;
		echo json_encode($respuesta);
		break;
		
		
			
	case 'grabarTickes';
		// @ Objetivo :
		// Grabar tickets temporales.
		$respuesta = array();
		$cabecera = array(); // Array que rellenamos de con POST
		$productos 					=$_POST['productos'];
		$cabecera['idTienda']		=$_POST['idTienda'];
		$cabecera['idCliente']		=$_POST['idCliente'];
		$cabecera['idUsuario'] 		=$_POST['idUsuario'];
		$cabecera['estadoTicket'] 	=$_POST['estadoTicket'];
		$cabecera['numTicket'] 		=$_POST['numTicket'];
		
		// Ahora recalculamos nuevamente
		$productos_para_recalculo = json_decode( json_encode( $_POST['productos'] ));
		$CalculoTotales = recalculoTotales($productos_para_recalculo);
		
		$nuevoArray = array(
						'desglose'=> $CalculoTotales['desglose'],
						'total' => $CalculoTotales['total']
							);
		
		//~ $CalculoTotales = gettype($productos);

		$res 	= grabarTicketsTemporales($BDTpv,$productos,$cabecera,$CalculoTotales['total']);
		$respuesta=$res;
		
		$respuesta = array_merge($respuesta,$nuevoArray);
		echo json_encode($respuesta);
		break;
}