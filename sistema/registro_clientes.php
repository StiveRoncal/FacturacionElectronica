<?php
 
    //Validar Usuario
    session_start();
    
    


    include "../conexion.php";
    //Validar campos vacios

    if(!empty($_POST)){
        $alert = '';
        if(empty($_POST['nombre']) || empty($_POST['telefono']) || empty($_POST['direccion'])){
            $alert = '<p class="msg_error">Todos los Campos son Obligatorios.</p>';
        }else{
          
            $dni        =  $_POST['dni'];
            $nombre     =  $_POST['nombre'];
            $telefono   =  $_POST['telefono'];
            $direccion  =  $_POST['direccion'];
            $usuario_id =  $_SESSION['idUser'];

            //Validar DNI y poner abrebiatura sin ello

            $result = 0;

            if(is_numeric($dni) and $dni != 0){
                $query = mysqli_query($conection,"SELECT * FROM cliente WHERE dni = '$dni' "); 
                $result = mysqli_fetch_array($query);
            }
            
            if($result > 0){
                $alert = '<p class="msg_error">El número de DNI ya existe.</p>';
            }else{
                $query_insert = mysqli_query($conection,"INSERT INTO cliente(dni,nombre,telefono,direccion,usuario_id) 
                                                        VALUES('$dni','$nombre','$telefono','$direccion','$usuario_id')");
                if($query_insert){
                    $alert = '<p class="msg_save">Cliente Guardado Correctamente.</p>';
                }else{
                    $alert = '<p class="msg_error">Error al Guardar Cliente.</p>';
                }
            }

            
        }
        mysqli_close($conection);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<?php include "includes/scripts.php";?>
	<title>Registro Cliente</title>
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/functions.js"></script>
</head>
<body>
	
	<?php include "includes/header.php";?>
	<section id="container">
		
        <div class="form_register">
            <h1><i class="fas fa-user-plus"></i> Registro Cliente</h1>
            <hr>
            <div class="alert"><?php echo isset($alert) ? $alert: ''; ?></div>

            <form action="" method="post">
                <label for="dni">DNI</label>
                <input type="number" name="dni" id="dni" placeholder="Ingrese DNI(Optional)">
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" placeholder="Nombre Completo">
                <label for="telefono">Telefono</label>
                <input type="number" name="telefono" id="telefono" placeholder="Ingrese Telefono">
                <label for="direccion">Dirección</label>
                <input type="text" name="direccion" id="direccion" placeholder="Direccion Completa">
                
                
                <button type="submit" class="btn_save"><i class="far fa-save"></i> Guardar Cliente</button>
            </form>

        </div>
	</section>

	
	<?php include "includes/footer.php";?>
</body>
</html>