<?php

session_start();
    
if($_SESSION['rol'] != 1){
    header("Location: ./");
}

include "../conexion.php";
    if(!empty($_POST)){

        //Proteger usuario admin 
        if($_POST['idusuario'] == 1){
            header("Location: lista_usuarios.php");
            mysqli_close($conection);
            exit;
        }
        $idusuario = $_POST['idusuario'];

        $query_delete = mysqli_query($conection,"UPDATE usuario SET estatus = 0 WHERE idusuario = $idusuario");
        mysqli_close($conection);

        if($query_delete){
            header("Location: lista_usuarios.php");
            
        }else{
            echo "Error al Eliminar";
        }

    }





    if(empty($_REQUEST['id']) || $_REQUEST['id'] == 1)
    {
        header("Location: lista_usuarios.php");
        mysqli_close($conection);
    }else{
       

        $idusuario =$_REQUEST['id'];

        $query = mysqli_query($conection,"SELECT u.nombre,u.usuario,r.rol
                                        FROM usuario u
                                        INNER JOIN
                                        rol r
                                        ON u.rol = r.idrol
                                        WHERE u.idusuario = $idusuario and estatus = 1");   
        mysqli_close($conection);
        $result = mysqli_num_rows($query);

        if($result > 0 ){
            while($data = mysqli_fetch_array($query)){
                $nombre  = $data['nombre'];
                $usuario = $data['usuario'];
                $rol     = $data['rol'];
             }
        }else{
            header("Location: lista_usuarios.php");
        }
    }



?>

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<?php include "includes/scripts.php";?>
	<title>Eliminar Usuario</title>
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
            <p>Nombre: <span><?php echo $nombre;?></span></p>
            <p>Usuario: <span><?php echo $usuario;?></span></p>
            <p>Tipo Usuario: <span><?php echo $rol;?></span></p>

            <form action="" method="POST">
                <input type="hidden" name="idusuario" value="<?php echo $idusuario; ?>">
                <a href="lista_usuarios.php" class="btn_cancel"><i class="fas fa-ban"></i> Cancelar</a>
              
                <button type="submit" class="btn_ok"><i class="far fa-trash-alt"></i> Eliminar</button>
            </form>
        </div>
	</section>

	
	<?php include "includes/footer.php";?>
</body>
</html>