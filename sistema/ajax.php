
<?php 
   
    include '../conexion.php';
    session_start();
    //print_r($_POST);exit;

    if(!empty($_POST)){
        //extarer datos del producto
        if($_POST['action'] == 'infoProducto'){
            
            $producto_id = $_POST['producto'];

            $query = mysqli_query($conection,"SELECT codproducto, descripcion, existencia, precio FROM producto 
                                            WHERE codproducto = $producto_id AND estatus = 1");
            
            mysqli_close($conection);

            $result = mysqli_num_rows($query);

            if($result > 0){
                $data = mysqli_fetch_assoc($query);
                echo json_encode($data,JSON_UNESCAPED_UNICODE);
                exit;
            }

            echo 'error';
            exit;
        }

        //Agregar  Productos a Entrada
        if($_POST['action'] == 'addProduct'){
            
            if(!empty($_POST['cantidad']) || !empty($_POST['precio']) || !empty($_POST['producto_id'])){

                $cantidad    = $_POST['cantidad'];
                $precio      = $_POST['precio'];
                $producto_id = $_POST['producto_id'];
                $usuario_id  = $_SESSION['idUser'];


                //consulta
                $query_insert = mysqli_query($conection,"INSERT INTO entradas(codproducto,cantidad,precio,usuario_id)
                                                            VALUES($producto_id,$cantidad,$precio,$usuario_id)");

                if($query_insert){
                    //Ejecutar Procedimiento almacenado
                    $query_upd = mysqli_query($conection,"CALL actualizar_precio_producto($cantidad,$precio,$producto_id)");

                    $result_pro = mysqli_num_rows($query_upd);

                    if($result_pro > 0){
                        $data = mysqli_fetch_assoc($query_upd);
                        $data['producto_id'] = $producto_id;
                        echo json_encode($data,JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                }else{
                    echo 'error';
                }
                mysqli_close($conection);
            }else{
                echo 'error';
            }
            exit;
        }

        //Eliminar Producto
        if($_POST['action'] == 'delProduct')
        {
            if(empty($_POST['producto_id']) || !is_numeric($_POST['producto_id'])){
                echo 'error';
            }else{

                $idproducto = $_POST['producto_id'];
                
                $query_delete = mysqli_query($conection,"UPDATE producto SET estatus = 0 WHERE codproducto = $idproducto");
                mysqli_close($conection);
                
                if($query_delete){
                    
                   echo 'ok';
                    
                }else{
                    echo 'error';
                }
            }
           echo 'error';
           exit;
        }

        // Buscar Cliente
        if($_POST['action'] == 'searchCliente'){
            
            if(!empty($_POST['cliente'])){

                $dni = $_POST['cliente'];

                $query = mysqli_query($conection,"SELECT * FROM cliente WHERE dni LIKE '$dni' and estatus = 1");

                $result = mysqli_num_rows($query);

                $data = '';

                if($result > 0){
                    $data = mysqli_fetch_assoc($query);
                }else{
                    $data = 0;
                }
                echo json_encode($data,JSON_UNESCAPED_UNICODE);
            }
            exit;
        }

        // Registrar cliente ventas
        if($_POST['action'] == 'addCliente'){
            $dni = $_POST['dni_cliente'];
            $nombre = $_POST['nom_cliente'];
            $telefono = $_POST['tel_cliente'];
            $direccion = $_POST['dir_cliente'];
            $usuario_id = $_SESSION['idUser'];
            
            $query_insert = mysqli_query($conection,"INSERT INTO cliente(dni,nombre,telefono,direccion,usuario_id) VALUES('$dni','$nombre','$telefono','$direccion','$usuario_id')");

            if($query_insert){
                $codCliente = mysqli_insert_id($conection);
                $msg = $codCliente;
            }else{
                $msg = 'error';
            }
            mysqli_close($conection);
            echo $msg;
            exit; 
        }
        
        // Agregar Producto al detalle temporal
        if($_POST['action'] == 'addProductoDetalle'){
            if(empty($_POST['producto']) || empty($_POST['cantidad'])){
                echo 'error';
            }else{
                $codproducto = $_POST['producto'];
                $cantidad = $_POST['cantidad'];
                $token = md5($_SESSION['idUser']);

                $query_iva = mysqli_query($conection,"SELECT iva FROM configuracion");
                $result_iva = mysqli_num_rows($query_iva);

                $query_detalle_temp = mysqli_query($conection,"CALL add_detalle_temp($codproducto,$cantidad,'$token')");
                $result = mysqli_num_rows($query_detalle_temp);

                $detalleTabla = '';
                $sub_total = 0;
                $iva = 0;
                $total = 0;
                $arrayData = array();

                if($result > 0){
                    if($result_iva  > 0){
                        $info_iva = mysqli_fetch_assoc($query_iva);
                        $iva = $info_iva['iva'];
                    }

                    while($data = mysqli_fetch_assoc($query_detalle_temp)){
                        $precioTotal = round($data['cantidad'] * $data['precio_venta'], 2);
                        $sub_total = round($sub_total + $precioTotal,2 );
                        $total = round($total + $precioTotal,2);

                        $detalleTabla .= '<tr>
                                            <td>'.$data['codproducto'].'</td>
                                            <td colspan="2">'.$data['descripcion'].'</td>
                                            <td class="textcenter">'.$data['cantidad'].'</td>
                                            <td class="textright">'.$data['precio_venta'].'</td>
                                            <td class="textright">'.$precioTotal.'</td>
                                            <td class="">
                                                <a class="link_delete" href="#" onclick="event.preventDefault(); del_product_detalle('.$data['correlativo'].');"><i class="far fa-trash-alt"></i></a>
                                            </td>
                                        </tr>';
                    }

                    $impuesto = round($sub_total * ($iva / 100), 2);
                    $tl_sniva = round($sub_total - $impuesto, 2);
                    $total = round($tl_sniva + $impuesto,2);
                    
                    $detalleTotales = '
                                    <tr>
                                    <td colspan="5" class="textright">SUBTOTAL S/.</td>
                                    <td class="textright">'.$tl_sniva.'</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="textright">IGV ('.$iva.'%).</td>
                                    <td class="textright">'.$impuesto.'</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="textright">TOTAL S/.</td>
                                    <td class="textright">'.$total.'</td>
                                </tr>';

                    $arrayData['detalle'] = $detalleTabla;
                    $arrayData['totales'] = $detalleTotales;

                    echo json_encode($arrayData,JSON_UNESCAPED_UNICODE);
                }else{
                    echo 'error';
                }
                mysqli_close($conection);
            }
            exit;
        }

        // Extrae datos del detalle temp
        if($_POST['action'] == 'serchForDetalle'){
            if(empty($_POST['user'])){
                echo 'error';
            }else{
                
                $token = md5($_SESSION['idUser']);

                $query = mysqli_query($conection,"SELECT tmp.correlativo,
                                                        tmp.token_user,
                                                        tmp.cantidad,
                                                        tmp.precio_venta,
                                                        p.codproducto,
                                                        p.descripcion
                                                        FROM detalle_temp tmp
                                                        INNER JOIN producto p
                                                        ON tmp.codproducto = p.codproducto
                                                        WHERE token_user = '$token' ");

              
                $result = mysqli_num_rows($query);

                $query_iva = mysqli_query($conection,"SELECT iva FROM configuracion");
                $result_iva = mysqli_num_rows($query_iva);

                $detalleTabla = '';
                $sub_total = 0;
                $iva = 0;
                $total = 0;
                $arrayData = array();

                if($result > 0){
                    if($result_iva  > 0){
                        $info_iva = mysqli_fetch_assoc($query_iva);
                        $iva = $info_iva['iva'];
                    }

                    while($data = mysqli_fetch_assoc($query)){
                        $precioTotal = round($data['cantidad'] * $data['precio_venta'], 2);
                        $sub_total = round($sub_total + $precioTotal,2 );
                        $total = round($total + $precioTotal,2);

                        $detalleTabla .= '<tr>
                                            <td>'.$data['codproducto'].'</td>
                                            <td colspan="2">'.$data['descripcion'].'</td>
                                            <td class="textcenter">'.$data['cantidad'].'</td>
                                            <td class="textright">'.$data['precio_venta'].'</td>
                                            <td class="textright">'.$precioTotal.'</td>
                                            <td class="">
                                                <a class="link_delete" href="#" onclick="event.preventDefault(); del_product_detalle('.$data['correlativo'].');"><i class="far fa-trash-alt"></i></a>
                                            </td>
                                        </tr>';
                    }

                    $impuesto = round($sub_total * ($iva / 100), 2);
                    $tl_sniva = round($sub_total - $impuesto, 2);
                    $total = round($tl_sniva + $impuesto,2);
                    
                    $detalleTotales = '
                                    <tr>
                                    <td colspan="5" class="textright">SUBTOTAL S/.</td>
                                    <td class="textright">'.$tl_sniva.'</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="textright">IGV ('.$iva.'%).</td>
                                    <td class="textright">'.$impuesto.'</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="textright">TOTAL S/.</td>
                                    <td class="textright">'.$total.'</td>
                                </tr>';

                    $arrayData['detalle'] = $detalleTabla;
                    $arrayData['totales'] = $detalleTotales;

                    echo json_encode($arrayData,JSON_UNESCAPED_UNICODE);
                }else{
                    echo 'error';
                }
                mysqli_close($conection);
            }
            exit;
        }


        // Extrae datos del detalle temp
        if($_POST['action'] == 'delProductoDetalle'){
            
            if(empty($_POST['id_detalle'])){
                echo 'error';
            }else{
                
                $id_detalle = $_POST['id_detalle'];
                $token = md5($_SESSION['idUser']);

               

                $query_iva = mysqli_query($conection,"SELECT iva FROM configuracion");
                $result_iva = mysqli_num_rows($query_iva);

                $query_detalle_temp = mysqli_query($conection,"CALL del_detalle_temp($id_detalle, '$token')");
                $result = mysqli_num_rows($query_detalle_temp);

                $detalleTabla = '';
                $sub_total = 0;
                $iva = 0;
                $total = 0;
                $arrayData = array();

                if($result > 0){
                    if($result_iva  > 0){
                        $info_iva = mysqli_fetch_assoc($query_iva);
                        $iva = $info_iva['iva'];
                    }

                    while($data = mysqli_fetch_assoc($query_detalle_temp)){
                        $precioTotal = round($data['cantidad'] * $data['precio_venta'], 2);
                        $sub_total = round($sub_total + $precioTotal,2 );
                        $total = round($total + $precioTotal,2);

                        $detalleTabla .= '<tr>
                                            <td>'.$data['codproducto'].'</td>
                                            <td colspan="2">'.$data['descripcion'].'</td>
                                            <td class="textcenter">'.$data['cantidad'].'</td>
                                            <td class="textright">'.$data['precio_venta'].'</td>
                                            <td class="textright">'.$precioTotal.'</td>
                                            <td class="">
                                                <a class="link_delete" href="#" onclick="event.preventDefault(); del_product_detalle('.$data['correlativo'].');"><i class="far fa-trash-alt"></i></a>
                                            </td>
                                        </tr>';
                    }

                    $impuesto = round($sub_total * ($iva / 100), 2);
                    $tl_sniva = round($sub_total - $impuesto, 2);
                    $total = round($tl_sniva + $impuesto,2);
                    
                    $detalleTotales = '
                                    <tr>
                                    <td colspan="5" class="textright">SUBTOTAL S/.</td>
                                    <td class="textright">'.$tl_sniva.'</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="textright">IGV ('.$iva.'%).</td>
                                    <td class="textright">'.$impuesto.'</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="textright">TOTAL S/.</td>
                                    <td class="textright">'.$total.'</td>
                                </tr>';

                    $arrayData['detalle'] = $detalleTabla;
                    $arrayData['totales'] = $detalleTotales;

                    echo json_encode($arrayData,JSON_UNESCAPED_UNICODE);
                }else{
                    echo 'error';
                }
                mysqli_close($conection);
            }
            exit;
        }

        // Anular Venta

        if($_POST['action'] == 'anularVenta'){
            $token = md5($_SESSION['idUser']);

            $query_del = mysqli_query($conection,"DELETE FROM detalle_temp WHERE token_user = '$token' ");
            mysqli_close($conection);

            if($query_del){
                echo 'ok';
            }else{
                echo 'error';
            }
            exit;
        }

        // Procesar Venta
        if($_POST['action'] == 'procesarVenta'){
           
            if(empty($_POST['codcliente'])){
                $codcliente = 1;    
            }else{
                $codcliente = $_POST['codcliente'];
            }

            // token
            $token = md5($_SESSION['idUser']);
            $usuario = $_SESSION['idUser'];

            $query = mysqli_query($conection,"SELECT * FROM detalle_temp WHERE token_user = '$token'");
            $result = mysqli_num_rows($query);

            // Seguridad para ningun tipo de error

            if($result > 0){
                // Ejecutamso procedimientio alamcenado
                $query_procesar = mysqli_query($conection,"CALL procesar_venta($usuario,$codcliente,'$token')");

                $result_detalle = mysqli_num_rows($query_procesar);

                // Validar
                if($result_detalle > 0){

                    $data = mysqli_fetch_array($query_procesar);
                    echo json_encode($data,JSON_UNESCAPED_UNICODE);
                }else{
                    echo "error";
                }
            }else{
                echo "error";
            }
            mysqli_close($conection);
            exit;
        }


        // Info Factura
       if($_POST['action'] == 'infoFactura'){

            if(!empty($_POST['nofactura'])){

                $nofactura = $_POST['nofactura'];

                $query = mysqli_query($conection,"SELECT * FROM factura WHERE nofactura = '$nofactura' AND estatus = 1");

                mysqli_close($conection);

                $result = mysqli_num_rows($query);

                if($result > 0){

                    $data = mysqli_fetch_assoc($query);
                    echo json_encode($data,JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
            echo "error";
            exit;
       }


        //Anular factura
        
        if($_POST['action'] == 'anularFactura'){
            if(!empty($_POST['noFactura'])){

                $noFactura = $_POST['noFactura'];

                $query_anular = mysqli_query($conection,"CALL anular_factura($noFactura)");
                mysqli_close($conection);
                $result = mysqli_num_rows($query_anular);

                if($result > 0){
                    $data = mysqli_fetch_assoc($query_anular);
                    echo json_encode($data,JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
            echo "error";
            exit;
        }

        // Cambiar Contraseña
        if($_POST['action'] == 'changePassword'){
            
            // Validacion de campos
            if(!empty($_POST['passActual']) && !empty($_POST['passNuevo'])){

                // Varibles
                $password = md5($_POST['passActual']);
                $newPass  = md5($_POST['passNuevo']);
                $idUser = $_SESSION['idUser'];

                $code = '';
                $msg = '';
                $arrData = array();

                $query_user = mysqli_query($conection,"SELECT * FROM usuario 
                                                    WHERE clave = '$password' AND 
                                                    idusuario = $idUser");
                $result = mysqli_num_rows($query_user);

                if($result > 0){

                    $query_update = mysqli_query($conection,"UPDATE usuario SET clave = '$newPass' WHERE idusuario = $idUser");

                    mysqli_close($conection);

                    if($query_update){
                        $code = '00';
                        $msg = "Su Contraseña se ha actualizado con éxito.";
                    }else{
                        $code = '2';
                        $msg = "No es posible Cambiar su Contraseña";
                    }

                }else{
                    $code = '1';
                    $msg = "La contraseña Actual es Incorrecta.";
                }

                $arrData = array('cod' => $code, 'msg' => $msg);
                echo json_encode($arrData,JSON_UNESCAPED_UNICODE);

            }else{
                echo "error";
            }
            exit;
        }

        // Actualizar Datos Empresa
        // Dato alertFormEmpresa Proviene de un input hidden
        if($_POST['action'] == 'updateDataEmpresa'){

   
            
            if(empty($_POST['txtRuc']) || empty($_POST['txtNombre']) || empty($_POST['txtTelEmpresa']) || empty($_POST['txtEmailEmpresa']) || empty($_POST['txtDirEmpresa']) || empty($_POST['txtIgv'])){

                $code = '1';
                $msg = "Todos los campos son obligatorios.";
            }else{
                $intRuc     =  intval($_POST['txtRuc']);
                $strNombre  =  $_POST['txtNombre'];
                $strRSocial =  $_POST['txtRSocial'];
                $intTel     =  intval($_POST['txtTelEmpresa']);
                $strEmail   =  $_POST['txtEmailEmpresa'];
                $strDir     =  $_POST['txtDirEmpresa'];
                $strIgv     =  $_POST['txtIgv'];

                $queryUpd = mysqli_query($conection,"UPDATE configuracion SET nit = $intRuc,
                                                                            nombre = '$strNombre',
                                                                            razon_social = '$strRSocial',
                                                                            telefono = $intTel,
                                                                            email = '$strEmail',
                                                                            direccion = '$strDir',
                                                                            iva = $strIgv
                                                                            WHERE id = 1");
                mysqli_close($conection);

                if($queryUpd){
                    $code = '00';
                    $msg = "Datos actualizados correctamente";
                }else{
                    $code = '2';
                    $msg = "Error al actualizar los Datos.";
                }
            }

            $arrData = array('cod' => $code, 'msg' => $msg);
            echo json_encode($arrData,JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    exit;
  
    
?>