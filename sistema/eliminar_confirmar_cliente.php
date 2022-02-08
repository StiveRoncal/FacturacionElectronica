<?php

session_start();
    
if($_SESSION['rol'] != 1 and $_SESSION['rol'] != 2 ){

    header("Location: ./");

}

include "../conexion.php";

    //Boton de Acciom
    if(!empty($_POST)){

        if(empty($_POST['idcliente'])){
            header("Location: lista_clientes.php");
            mysqli_close($conection);
        }

        $idcliente = $_POST['idcliente'];

        $query_delete = mysqli_query($conection,"UPDATE cliente SET estatus = 0 WHERE idcliente = $idcliente");
        mysqli_close($conection);

        if($query_delete){

            header("Location: lista_clientes.php");
            
        }else{
            echo "Error al Eliminar";
        }

    }





    if(empty($_REQUEST['id']))
    {
        header("Location: lista_clientes.php");
        mysqli_close($conection);
    }else{
       

        $idcliente =$_REQUEST['id'];

        $query = mysqli_query($conection,"SELECT * FROM cliente WHERE idcliente = $idcliente and estatus = 1");   
        mysqli_close($conection);
        $result = mysqli_num_rows($query);

        if($result > 0 ){
            while($data = mysqli_fetch_array($query)){
                $dni = $data['dni'];
                $nombre  = $data['nombre'];
                
             }
        }else{
            header("Location: lista_clientes.php");
        }
    }



?>

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<?php include "includes/scripts.php";?>
	<title>Eliminar Cliente</title>
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/functions.js"></script>
</head>
<body>
	
	<?php include "includes/header.php";?>
	<section id="container">
		<div class="data_delete">
        <i class="fas fa-user-times fa-7x" style="color:#e66262"></i>
            <br>
            <br>
            <h2>¿Esta seguro de eliminar el siguiente registro?</h2>
            <p>Nombre del Cliente: <span><?php echo $nombre;?></span></p>
            <p>Dni: <span><?php echo $dni;?></span></p>
         

            <form action="" method="POST">
                <input type="hidden" name="idcliente" value="<?php echo $idcliente; ?>">
                <a href="lista_clientes.php" class="btn_cancel">Cancelar</a>
                <input type="submit" value="Eliminar" class="btn_ok">
            </form>
        </div>
	</section>

	
	<?php include "includes/footer.php";?>
</body>
</html>