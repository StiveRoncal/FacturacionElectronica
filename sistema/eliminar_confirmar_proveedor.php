<?php

session_start();
    
if($_SESSION['rol'] != 1 and $_SESSION['rol'] != 2 ){

    header("Location: ./");

}

include "../conexion.php";

    //Boton de Acciom
    if(!empty($_POST)){

        if(empty($_POST['idproveedor'])){
            header("Location: lista_proveedor.php");
            mysqli_close($conection);
        }

        $idproveedor = $_POST['idproveedor'];

        $query_delete = mysqli_query($conection,"UPDATE proveedor SET estatus = 0 WHERE codproveedor = $idproveedor");
        mysqli_close($conection);

        if($query_delete){

            header("Location: lista_proveedor.php");
            
        }else{
            echo "Error al Eliminar";
        }

    }




    //Mostrar Datos
    if(empty($_REQUEST['id']))
    {
        header("Location: lista_proveedor.php");
        mysqli_close($conection);
    }else{
       

        $idproveedor =$_REQUEST['id'];

        $query = mysqli_query($conection,"SELECT * FROM proveedor WHERE codproveedor = $idproveedor and estatus = 1");   
        mysqli_close($conection);
        $result = mysqli_num_rows($query);

        if($result > 0 ){
            while($data = mysqli_fetch_array($query)){
                $proveedor = $data['proveedor'];
                
                
             }
        }else{
            header("Location: lista_proveedor.php");
        }
    }



?>

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<?php include "includes/scripts.php";?>
	<title>Eliminar Proveedor</title>
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/functions.js"></script>
</head>
<body>
	
	<?php include "includes/header.php";?>
	<section id="container">
		<div class="data_delete">
            <i class="far fa-building fa-7x" style="color:#e66262"></i>
            <br><br>
            <h2>¿Esta seguro de eliminar el siguiente registro?</h2>
            <p>Nombre del Proveedor: <span><?php echo $proveedor;?></span></p>
           
         

            <form action="" method="POST">
                <input type="hidden" name="idproveedor" value="<?php echo $idproveedor; ?>">
                <a href="lista_proveedor.php" class="btn_cancel">Cancelar</a>
                <input type="submit" value="Eliminar" class="btn_ok">
            </form>
        </div>
	</section>

	
	<?php include "includes/footer.php";?>
</body>
</html>