<?php

session_start();
if($_SESSION['rol'] != 1){
    header("Location: ./");
}

include "../conexion.php";
    //Validar campos vacios

    //Boton Actualizar Usuario
    if(!empty($_POST)){
       $alert = '';

       if(empty($_POST['proveedor']) ||empty($_POST['contacto']) || empty($_POST['telefono']) || empty($_POST['direccion'])){

           $alert = '<p class="msg_error">Todo los campos son Obligatorios</p>';

       }else{

           $idproveedor = $_POST['id'];
           $proveedor   = $_POST['proveedor'];
           $contacto    = $_POST['contacto'];
           $telefono    = $_POST['telefono'];
           $direccion   = $_POST['direccion'];

          
           
          

               $sql_update = mysqli_query($conection,"UPDATE proveedor
                                                    SET proveedor = '$proveedor', contacto = '$contacto',
                                                    telefono = '$telefono',
                                                    direccion = '$direccion' 
                                                    WHERE codproveedor = $idproveedor");
                if($sql_update){
                    $alert = '<p class="msg_save">Cliente Actualizado Correctamente.</p>';
                }else{
                    $alert = '<p class="msg_error">Error al Actualizar El Cliente</p>';
                }
            
           }
    
       }
      
    

    //Mostrar Datos

    if(empty($_REQUEST['id'])){
        header('Location: lista_proveedor.php');
        mysqli_close($conection);
    }

        $idproveedor = $_REQUEST['id'];

        $sql = mysqli_query($conection,"SELECT * FROM proveedor WHERE codproveedor = $idproveedor and estatus = 1 ");
        mysqli_close($conection);
        $result_sql = mysqli_num_rows($sql);

        if($result_sql == 0){
            header('Location: lista_proveedor.php');
        }else{
            $option = '';
            while($data = mysqli_fetch_array($sql)){
                $idproveedor   = $data['codproveedor'];
                $proveedor   = $data['proveedor'];
                $contacto    = $data['contacto'];
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
	<title>Actualizar Proveedor</title>
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/functions.js"></script>
</head>
<body>
	
	<?php include "includes/header.php";?>
	<section id="container">
		
        <div class="form_register">
            <h1><i class="far fa-edit"></i> Actualizar Proveedor</h1>
            <hr>
            <div class="alert"><?php echo isset($alert) ? $alert: ''; ?></div>

            <form action="" method="post">
                <input type="hidden" name="id" value="<?php echo $idproveedor;?>"> 
                <label for="proveedor">Nombre Proveedor</label>
                <input type="text" name="proveedor" id="proveedor" placeholder="Nombre Empresa Proveedor" value="<?php echo $proveedor;?>">
                <label for="contacto">Contacto</label>
                <input type="text" name="contacto" id="contacto" placeholder="Nombre Completo del Proveedor" value="<?php echo $contacto;?>">
                <label for="telefono">Telefono</label>
                <input type="number" name="telefono" id="telefono" placeholder="Ingrese Telefono" value="<?php echo $telefono;?>">
                <label for="direccion">Dirección</label>
                <input type="text" name="direccion" id="direccion" placeholder="Direccion Completa" value="<?php echo $direccion;?>">
                
               
                <button type="submit" class="btn_save"><i class="far fa-edit"></i> Actualizar Proveedor</button>
            </form>

        </div>
	</section>

	
	<?php include "includes/footer.php";?>
</body>
</html>