<?php

session_start();
    

include "../conexion.php";
    //Validar campos vacios

    //Boton Actualizar Usuario
    if(!empty($_POST)){
       $alert = '';

       if(empty($_POST['nombre']) || empty($_POST['telefono']) || empty($_POST['direccion'])){

           $alert = '<p class="msg_error">Todo los campos son Obligatorios</p>';

       }else{

           $idCliente  = $_POST['id'];
           $dni        = $_POST['dni'];
           $nombre     = $_POST['nombre'];
           $telefono   = $_POST['telefono'];
           $direccion  = $_POST['direccion'];

           $result = 0;

           if(is_numeric($dni) and $dni !=0 ){

               $query = mysqli_query($conection,"SELECT * FROM cliente 
                                                WHERE ( dni = '$dni' AND idcliente != $idCliente)");

                $result = mysqli_fetch_array($query);
                

           }
           
           if($result > 0){
               
               $alert = '<p class="msg_error">El Dni ya Existe, Ingrese Otro</p>';
           }else{

               if($dni == ''){
                   $dni = 0;
               }

               $sql_update = mysqli_query($conection,"UPDATE cliente
                                                    SET dni = $dni, nombre = '$nombre', telefono = '$telefono',
                                                    direccion = '$direccion' 
                                                    WHERE idcliente = $idCliente");
                if($sql_update){
                    $alert = '<p class="msg_save">Cliente Actualizado Correctamente.</p>';
                }else{
                    $alert = '<p class="msg_error">Error al Actualizar El Cliente</p>';
                }
            
           }
    
       }
      
    }

    //Mostrar Datos

    if(empty($_REQUEST['id'])){
        header('Location: lista_clientes.php');
        mysqli_close($conection);
    }

        $idcliente = $_REQUEST['id'];

        $sql = mysqli_query($conection,"SELECT * FROM cliente WHERE idcliente = $idcliente and estatus = 1 ");
        mysqli_close($conection);
        $result_sql = mysqli_num_rows($sql);

        if($result_sql == 0){
            header('Location: lista_clientes.php');
        }else{
            $option = '';
            while($data = mysqli_fetch_array($sql)){
                $idcliente   = $data['idcliente'];
                $dni         = $data['dni'];
                $nombre      = $data['nombre'];
                $telefono    = $data['telefono'];
                $direccion   = $data['direccion'];
               

                
            }
        }
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<?php include "includes/scripts.php";?>
	<title>Actualizar Cliente</title>
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/functions.js"></script>
</head>
<body>
	
	<?php include "includes/header.php";?>
	<section id="container">
		
        <div class="form_register">
            <h1><i class="far fa-edit"></i> Actualizar Cliente</h1>
            <hr>
            <div class="alert"><?php echo isset($alert) ? $alert: ''; ?></div>

            <form action="" method="post">
                <input type="hidden" name="id" value="<?php echo $idcliente; ?>">
                <label for="dni">DNI</label>
                <input type="number" name="dni" id="dni" placeholder="Ingrese DNI(Optional)" value="<?php echo $dni;?>">
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" placeholder="Nombre Completo"  value="<?php echo $nombre;?>">
                <label for="telefono">Telefono</label>
                <input type="number" name="telefono" id="telefono" placeholder="Ingrese Telefono"  value="<?php echo $telefono;?>">
                <label for="direccion">Dirección</label>
                <input type="text" name="direccion" id="direccion" placeholder="Direccion Completa"  value="<?php echo $direccion;?>">
                
               
                <button type="submit" class="btn_save"><i class="far fa-edit"></i> Actualizar Cliente</button>
            </form>

        </div>
	</section>

	
	<?php include "includes/footer.php";?>
</body>
</html>