<?php
/* Fichero de tareas a realizar.
 * 
 * 
 * Con el switch al final y variable $pulsado
 * 
 *  */
/* ===============  REALIZAMOS CONEXIONES  ===============*/
$pulsado = $_POST['pulsado'];
include_once ("./../../configuracion.php");
// Crealizamos conexion a la BD Datos
include_once ("./../mod_conexion/conexionBaseDatos.php");
// Incluimos funciones
include_once ("./funciones.php");
include_once("clases/pedidosVentas.php");
$CcliPed=new PedidosVentas($BDTpv);
include_once("../../clases/producto.php");
$Cprod=new Producto($BDTpv);
include_once ("clases/albaranesVentas.php");
$CalbAl=new AlbaranesVentas($BDTpv);
include_once("../../clases/cliente.php");
$Ccliente=new Cliente($BDTpv);
include_once("clases/facturasVentas.php");
$CFac=new FacturasVentas($BDTpv);
switch ($pulsado) {
  
		case 'buscarProductos':
		//@Objetivo: Buscar productos y diferenciar si tenemos que mostrar modal pintar la linea directamente
			$busqueda = $_POST['valorCampo'];
			$campoAbuscar = $_POST['campo'];
			$id_input = $_POST['cajaInput'];
			$idcaja=$_POST['idcaja'];
			$dedonde=$_POST['dedonde'];
			$res = BuscarProductos($id_input,$campoAbuscar, $idcaja, $busqueda,$BDTpv);
			if ($res['Nitems']===1){
				$respuesta=$res;
				$respuesta['Nitems']=$res['Nitems'];	
			}else{
				// Cambio estado para devolver que es listado.
				$respuesta['listado']= htmlProductos($res['datos'],$id_input,$campoAbuscar,$busqueda, $dedonde);
				$respuesta['Estado'] = 'Listado';
				$respuesta['datos']=$res['datos'];
			}
			echo json_encode($respuesta);  
		break;
		
	    case 'buscarClientes':
			//@Objetivo
			//BUsqueda de clientes , si recibe de una caja id lo busca directamente si no crea el modal de clientes 
			$busqueda = $_POST['busqueda'];
			$dedonde = $_POST['dedonde'];
			$idcaja=$_POST['idcaja'];
			$tabla='clientes';
			if ($idcaja=="id_cliente"){
				$res=$Ccliente->DatosClientePorId($busqueda);
				if ($res){
					$respuesta['res']=$res;
					$respuesta['id']=$res['idClientes'];
					$respuesta['nombre']=$res['Nombre'];
					$respuesta['Nitems']=1;
					$respuesta['formasVenci']=$res['fomasVenci'];
				}else{
					$respuesta['Nitems']=2;
				}
				
			}else{
				$buscarTodo=$Ccliente->BuscarClientePorNombre($busqueda);
				$respuesta['html'] = htmlClientes($busqueda,$dedonde, $idcaja, $buscarTodo['datos']);
				$respuesta['datos']=$buscarTodo['datos'];
			}
			echo json_encode($respuesta);
		break;	
		
		
		
		case 'buscarPedido':
		//@Objetivo:
		//BUscar los pedidos guardado de un cliente para el apartado albaranes, si el pedido que inserto existe guarda los datos de este
		//Si no muestra un modal con los pedidos guardados de ese cliente
	
			$busqueda=$_POST['busqueda'];
			$idCliente=$_POST['idCliente'];
			$res=$CcliPed->PedidosClienteGuardado($busqueda, $idCliente);
			$respuesta['res']=$res;
			if ($res['Nitem']==1){
				$temporales=$CcliPed->contarPedidosTemporal($res['id']);
				if ($temporales['numPedTemp']==0){
					$respuesta['temporales']=$temporales;
					$respuesta['datos']['Numpedcli']=$res['Numpedcli'];
					$respuesta['datos']['idPedCli']=$res['id'];
					$respuesta['datos']['idPedido']=$res['id'];
					$respuesta['datos']['fecha']=$res['FechaPedido'];
					$respuesta['datos']['total']=$res['total'];
					$respuesta['datos']['estado']="Activo";
					$respuesta['Nitems']=$res['Nitem'];
					$productosPedido=$CcliPed->ProductosPedidos($res['id']);
					$respuesta['productos']=$productosPedido;
				}
			}else{
				$respuesta=$res;
				$modal=modalAdjunto($res['datos']);
				$respuesta['html']=$modal['html'];
				
			}
			echo json_encode($respuesta);
		break;
		
		case 'buscarAlbaran':
		//Objetivo:
		//Busca el albarán indicado, si recibe resultado guarda el albaran y muestra los productos de este 
		//Si no muestra un albarán
			$busqueda=$_POST['busqueda'];
			$idCliente=$_POST['idCliente'];
			$res=$CalbAl->AlbaranClienteGuardado($busqueda, $idCliente);
			if ($res['Nitem']==1){
					
					$respuesta['temporales']=1;
					$respuesta['datos']['Numalbcli']=$res['Numalbcli'];
					$respuesta['datos']['idalbcli']=$res['id'];
					$respuesta['datos']['fecha']=$res['Fecha'];
					$respuesta['datos']['total']=$res['total'];
					$respuesta['datos']['idAlbaran']=$res['id'];
					$respuesta['datos']['estado']="Activo";
					$respuesta['Nitems']=$res['Nitem'];
					$productosAlbaran=$CalbAl->ProductosAlbaran($res['id']);
					$respuesta['productos']=$productosAlbaran;
				
			}else{
				$respuesta=$res;
				$modal=modalAdjunto($res['datos']);
				$respuesta['html']=$modal['html'];
				
			}
			echo json_encode($respuesta);
		break;
		case 'anhadirPedidoTemp':
		//@Objetivo:
		//añadir un pedido temporal, si existe se modifica y si no se inserta
		//A continuación se calculan los totales y desgloses 
		$idTemporal=$_POST['idTemporal'];
		$idUsuario=$_POST['idUsuario'];
		$idTienda=$_POST['idTienda'];
		$estado=$_POST['estado'];
		$fecha=$_POST['fecha'];
		$idReal=$_POST['idReal'];
		$idCliente=$_POST['idCliente'];
		$productos=$_POST['productos'];
		$existe=0;
		if ($idTemporal>0){
			$res=$CcliPed->ModificarPedidoTemp($idCliente, $idTemporal, $idTienda, $idUsuario, $estado, $idReal, $productos);
			
		}else{
			$res=$CcliPed->addPedidoTemp($idCliente,  $idTienda, $idUsuario, $estado, $idReal, $productos);
			$idTemporal=$res['id'];
			$respuesta['sql']=$res['sql'];
		}
		if ($idReal>0){
			$modNum=$CcliPed->ModIdReal($idTemporal, $idReal);
		}
		if ($productos){
				$productos_para_recalculo = json_decode( json_encode( $productos ));
				$respuesta['productosre']=$productos_para_recalculo;
				$CalculoTotales = recalculoTotales($productos_para_recalculo);
				$total=round($CalculoTotales['total'],2);
				$respuesta['total']=round($CalculoTotales['total'],2);
				$respuesta['totales']=$CalculoTotales;
				$modTotal=$CcliPed->modTotales($idTemporal, $respuesta['total'], $CalculoTotales['subivas']);
			
				$htmlTotales=htmlTotales($CalculoTotales);
				$respuesta['htmlTabla']=$htmlTotales['html'];
			}
			$respuesta['id']=$idTemporal;
			$respuesta['existe']=$existe;
			$respuesta['productos']=$_POST['productos'];
		echo json_encode($respuesta);
		break;
		
		
		case 'anhadirAlbaranTemporal':
		//@Objetivo:
		//añadir albarán temporal, hace las comprobaciones necesarias.
			$idAlbaranTemp=$_POST['idTemporal'];
			$idUsuario=$_POST['idUsuario'];
			$idTienda=$_POST['idTienda'];
			$estadoAlbaran=$_POST['estado'];
			$fecha=$_POST['fecha'];
			$pedidos=$_POST['pedidos'];
			$productos=$_POST['productos'];
			$idCliente=$_POST['idCliente'];
			$idReal=$_POST['idReal'];
			$existe=0;
			//Si el número del albarán real existe lo guardamos
			if ($numAlbaran>0){
				$albaran=$CalbAl->buscarTemporalNumReal($numAlbaran);
				$idAlbaranTemp=$albaran['id'];
			}
			//Si el albarán temporal existe lo modifica
			if ($idAlbaranTemp>0){
				$rest=$CalbAl->modificarDatosAlbaranTemporal($idUsuario, $idTienda, $estadoAlbaran, $fecha , $pedidos, $idAlbaranTemp, $productos);
				$existe=1;
				$respuesta['sql']=$rest['sql'];
				$res=$rest['idTemporal'];
				$pro=$rest['productos'];
			}else{
				//Si no lo inserta
				$rest=$CalbAl->insertarDatosAlbaranTemporal($idUsuario, $idTienda, $estadoAlbaran, $fecha , $pedidos, $productos, $idCliente);
				$existe=0;
				$pro=$rest['productos'];
				$res=$rest['id'];
				$idAlbaranTemp=$res;
			}
			//$respuesta['numalbaran']=$numAlbaran;
			if ($idReal>0){
				$modId=$CalbAl->addNumRealTemporal($idAlbaranTemp, $idReal);
				$respuesta['sqlmodnum']=$modId;
			}
			//recalcula los totales de los productos y modifica el total en albarán temporal
			if ($productos){
				$productos_para_recalculo = json_decode( json_encode( $_POST['productos'] ));
				$respuesta['productosre']=$productos_para_recalculo;
				$CalculoTotales = recalculoTotales($productos_para_recalculo);
				$total=round($CalculoTotales['total'],2);
				$respuesta['total']=round($CalculoTotales['total'],2);
				$respuesta['totales']=$CalculoTotales;
				$modTotal=$CalbAl->modTotales($res, $respuesta['total'], $CalculoTotales['subivas']);
				$htmlTotales=htmlTotales($CalculoTotales);
				$respuesta['htmlTabla']=$htmlTotales['html'];
			}
			$respuesta['id']=$res;
			$respuesta['existe']=$existe;
			$respuesta['productos']=$_POST['productos'];
			
			echo json_encode($respuesta);
		break;
		
		
		case 'anhadirfacturaTemporal':
		//@Objetivo:
		//Añadir factura temporal hace exactamente lo mismo que el añadir albarán temporal pero esta vez con facturas
			$idFacturaTemp=$_POST['idTemporal'];
			$idUsuario=$_POST['idUsuario'];
			$idTienda=$_POST['idTienda'];
			$estadoFactura=$_POST['estado'];
			$numFactura=$_POST['idReal'];
			$fecha=$_POST['fecha'];
			$albaranes=$_POST['albaranes'];
			$productos=$_POST['productos'];
			$idCliente=$_POST['idCliente'];
			
			$existe=0;
			if ($numFactura>0){
				$factura=$CFac->buscarTemporalNumReal($numFactura);
				$idFacturaTemp=$factura['id'];
			}
			if ($idFacturaTemp>0){
				$rest=$CFac->modificarDatosFacturaTemporal($idUsuario, $idTienda, $estadoFactura, $fecha , $albaranes, $idFacturaTemp, $productos);
				$existe=1;
				
				$res=$rest['idTemporal'];
				$pro=$rest['productos'];
			}else{
				$rest=$CFac->insertarDatosFacturaTemporal($idUsuario, $idTienda, $estadoFactura, $fecha , $albaranes, $productos, $idCliente);
				$existe=0;
				$pro=$rest['productos'];
				$res=$rest['id'];
				$idFacturaTemp=$res;
				
			}
			$respuesta['numFactura']=$numFactura;
			if ($numFactura>0){
				$modId=$CFac->addNumRealTemporal($idFacturaTemp, $numFactura);
				
			}
			if ($productos){
				$productos_para_recalculo = json_decode( json_encode( $_POST['productos'] ));
				$respuesta['productosre']=$productos_para_recalculo;
				$CalculoTotales = recalculoTotales($productos_para_recalculo);
				$total=round($CalculoTotales['total'],2);
				$respuesta['total']=round($CalculoTotales['total'],2);
				$respuesta['totales']=$CalculoTotales;
				$modTotal=$CFac->modTotales($res, $respuesta['total'], $CalculoTotales['subivas']);
				$htmlTotales=htmlTotales($CalculoTotales);
				$respuesta['htmlTabla']=$htmlTotales['html'];
				
			}
			$respuesta['id']=$res;
			$respuesta['existe']=$existe;
			$respuesta['productos']=$_POST['productos'];
			
			echo json_encode($respuesta);
		break;
			case 'modificarEstadoPedido':
		//Objetivo:
		//Modificar el estado de un pedido a Sin Guardar si viene de pedidos , si viene de albarán a facturado
		//Y si viene de factura entonces no es un pedido es un albarán que lo pasa a facturado
			$idPedido=$_POST['idModificar'];
			$estado=$_POST['estado'];
			$modEstado=$CcliPed->ModificarEstadoPedido($idPedido, $estado);
			//~ if (isset ($respuesta)){
				//~ echo json_encode($respuesta);
			//~ }
		
		break;
		
		case 'comprobarPedidos':
		//Objetivo:
		//Comprobar los pedidos en estado guardado que son de un cliente
			$idCliente=$_POST['idCliente'];
			$estado="Guardado";
			if ($idCliente>0){
				$comprobar=$CcliPed->ComprobarPedidos($idCliente, $estado);
				if (isset ($comprobar['ped'])){
					if ($comprobar['ped']==1){
						$respuesta['ped']=1;
					
					}else{
						$respuesta['ped']=0;
					}
				}else{
					$respuesta['ped']=0;
				}
				
			}
			echo json_encode($respuesta);
		break;
		
		case 'comprobarAlbaran':
		//Objetivo:
		//Comprobar los albaranes con estado guardado que son del cliente seleccionado
		$idCliente=$_POST['idCliente'];
			$estado="Guardado";
			if ($idCliente>0){
				$comprobar=$CalbAl->ComprobarAlbaranes($idCliente, $estado);
				if (isset ($comprobar['alb'])){
					if ($comprobar['alb']==1){
						$respuesta['alb']=1;
						
					}else{
						$respuesta['alb']=0;
						
					}	
				}else{
					$respuesta['alb']=0;
					$respuesta['sql']=$comprobar['sql'];
				}
				
			}
			echo json_encode($respuesta);
		break;
		
		
		case 'htmlAgregarFilaPedido':
		//Objetivo:
		//Devuelve el html de la fila del pedido 
		//	$res=lineaPedidoAlbaran($_POST['datos'], $_POST['dedonde']);
		$res=htmlLineaPedidoAlbaran($_POST['datos'], $_POST['dedonde']);
			$respuesta['html']=$res['html'];
			echo json_encode($respuesta);
		break;
		
		case 'htmlAgregarFilaAlbaran':
		//Objetivo:
		//Devuelve el html de la fila albarán
		//	$res=lineaAlbaranFactura($_POST['datos'], $_POST['dedonde']);
		$arrayAlbaranes=array();
		array_push($arrayAlbaranes, $_POST['datos']);
		$res=htmlAlbaranFactura($arrayAlbaranes, $_POST['dedonde']);
			$respuesta['html']=$res['html'];
			echo json_encode($respuesta);
		break;
		
		case 'htmlAgregarFilasProductos':
		//Objetivo:
		//HTML que va mostrando las filas de los pedidos en un albarán
		$productos=$_POST['productos'];
		$dedonde=$_POST['dedonde'];
		$respuesta =array('html'=>'');
			 foreach($productos as $producto){
				if (!is_array($producto)){
					$bandera=1;
				}else{
				$res=htmlLineaPedidoAlbaran($producto, $dedonde);
				$respuesta['html'].=$res;
				}
		 }
		 if ($bandera==1){
			 $res=htmlLineaPedidoAlbaran($productos, $dedonde);
				 $respuesta['html']=$res;
		 }
		echo json_encode($respuesta);
		break;
		
		case 'buscarDatosPedido':
		//@Objetivo:
		 //Busca los datos de un pedido en concreto
			$idPedido=$_POST['idPedido'];
			$res=$CcliPed->datosPedidos($idPedido);
			$respuesta['NumPedido']=$res['Numpedcli'];
			echo json_encode($respuesta);
		break;
		
		case 'htmlFomasVenci':
			//@Objetivo:
			//MUestra las formas de vencimiento de esa factura
			$formasVenci=$_POST['formasVenci'];
			if ($_POST['formasVenci']){
				$formaPago=json_decode($formasVenci, true);
				$forma=$formaPago['formapago'];
				$venci=$formaPago['vencimiento'];
			}else{
				$forma=0;
				$venci=0;
			}
			
			$for=htmlFormasVenci($forma, $BDTpv);
			$respuesta['html1']=$for['html'];
			$fun=fechaVencimiento($venci, $BDTpv);
			$ven=htmlVencimiento($fun, $BDTpv);
			$respuesta['html2']=$ven['html'];
			$respuesta['fecha']=$fun;
			echo json_encode($respuesta);
		break;
		
		
		case 'ModificarFormasVencimiento':
		//@Objetivo:
		//MOdificar la forma de vencimiento de esa factura en concreto
		$opcion=$_POST['opcion'];
		$fechaVenci=$_POST['fechaVenci'];
		$idTemporal=$_POST['idFacTem'];
		$formasVenci=array();
		$formasVenci['forma']=$opcion;
		$formasVenci['fechaVencimiento']=$fechaVenci;
		
		$json=json_encode($formasVenci);
		
		if ($idTemporal>0){
			$modTemporal=$CFac->formasVencimientoTemporal($idTemporal, $json);
		}
		$respuesta=$json;
		echo json_encode($modTemporal);
		break;
		
		case 'modificarEstadoFactura':
		//@Objetivo:
		//Modificar el estado de una factura 
		$idFactura=$_POST['idModificar'];
		$estado=$_POST['estado'];
		$modEstado=$CFac->modificarEstado($idFactura, $estado);
		echo json_encode($modEstado);
		break;
		
		
		case 'modificarEstadoAlbaran':
		//@Objetivo:
		//modificar el estado de un alabrán
		$idAlbaran=$_POST['idModificar'];
		$estado=$_POST['estado'];
		$modEstado=$CalbAl->ModificarEstadoAlbaran($idAlbaran, $estado);
		echo json_encode($modEstado);
		break;
		
		case 'insertarImporte':
		//@Objetivo:
		//Insertar un nuevo importe a una factura
		 $importe=$_POST['importe'];
		 $fecha=$_POST['fecha'];
		 $idFactura=$_POST['idTemporal'];
		 $formaPago=$_POST['forma'];
		 $referencia=$_POST['referencia'];
		 $total=$_POST['total'];
		 $idReal=$_POST['idReal'];
		
		 $arrayPrincipal=array();
		 $error=0;
		 $bandera=$importe;
		 $importesReal=$CFac->importesFactura($idReal);
		 $respuesta['importeReal']=$importesReal;
		 if(count($importesReal)>0){
			 $importesReal=modificarArraysImportes($importesReal, $total);
			$importesTemporal=json_encode($importesReal);
			$eliminarReal=$CFac->eliminarRealImportes($idReal);
			$respuesta['impTemporal']=$importesTemporal;
		 }else{
			 $importesTemporal=$CFac->importesTemporal($idFactura);
			 $importesTemporal=$importesTemporal['FacCobros'];
			 $bandera=$importe;
		 }
		 
		 if ($importesTemporal){
			
			$importes=json_decode($importesTemporal, true);
			$respuesta['importes']= $importes;
			 foreach ($importes as $import){
				 $bandera=$bandera+(string)$import['importe'];
				 array_push($arrayPrincipal, $import);
			 }
			 
			 if ($bandera>$total){
				 $respuesta['mensaje']=1;
				 $error=1;
			 }
			 $respuesta['bandera']=$bandera;
		 }
		 if ($error==0){
		$pendiente=$total-$bandera;
		$nuevo=array();
		$nuevo['importe']=$importe;
		$nuevo['fecha']=$fecha;
		$nuevo['forma']=$formaPago;
		$nuevo['referencia']=$referencia;
		$nuevo['pendiente']=$pendiente;
		$respuesta['nuevo']=$nuevo;
		array_push($arrayPrincipal, $nuevo);
		$jsonImporte=json_encode($arrayPrincipal);
		$modImportes=$CFac->modificarImportesTemporal($idFactura, $jsonImporte);
		$respuesta['sqlmod']=$modImportes;
		$html=htmlImporteFactura($nuevo, $BDTpv);
		$respuesta['html']=$html['html'];
	}
		echo json_encode($respuesta);
		break;
		//@Objetivo:
		//enviar los datos para imprimir el pdf
		case 'datosImprimir':
		$id=$_POST['id'];
		$dedonde=$_POST['dedonde'];
		$tienda=$_POST['tienda'];
		$nombreTmp=$dedonde."ventas.pdf";
		$htmlImprimir=montarHTMLimprimir($id, $BDTpv, $dedonde, $tienda);
		$cabecera=$htmlImprimir['cabecera'];
		$html=$htmlImprimir['html'];
		require_once('../../lib/tcpdf/tcpdf.php');
		include ('../../clases/imprimir.php');
		include('../../controllers/planImprimir.php');
		$ficheroCompleto=$rutatmp.'/'.$nombreTmp;
		echo json_encode($ficheroCompleto);
		break;
		
		
		
}
