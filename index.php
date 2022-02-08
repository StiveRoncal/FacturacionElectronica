<?php
    $alert ='';
    session_start();





if(!empty($_SESSION['active'])){
    header('Location: sistema/');
}else{
    
    
    if(!empty($_POST)){
        if(empty($_POST['usuario']) || empty($_POST['clave'])){

            $alert = 'Ingrese su usuario y su Clave ';

        }else{

            require_once "conexion.php";

            $user = mysqli_real_escape_string($conection, $_POST['usuario']);
            $pass = md5(mysqli_real_escape_string($conection,$_POST['clave']));

         

            $query = mysqli_query($conection,"SELECT u.idusuario, u.nombre, u.correo, u.usuario, r.idrol,
                                                 r.rol
                                            FROM usuario u
                                            INNER JOIN rol r
                                            ON u.rol = r.idrol
                                            WHERE u.usuario = '$user' AND u.clave  = '$pass' AND u.estatus = 1");
            mysqli_close($conection);
            $result = mysqli_num_rows($query);

            if($result > 0){
                $data = mysqli_fetch_array($query);
             
                $_SESSION['active'] = true;
                $_SESSION['idUser'] = $data['idusuario'];
                $_SESSION['nombre'] = $data['nombre'];
                $_SESSION['email'] = $data['correo'];
                $_SESSION['user'] = $data['usuario'];
                $_SESSION['rol'] = $data['idrol'];
                $_SESSION['rol_name'] = $data['rol'];

                header('Location: sistema/');
            }else{
                $alert = 'El usuario o la clave son Incorrectos';
                session_destroy();
            }
        }
    }

}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, user-scalable=no , initial-scale=1.0, maximum-scale=1.0, minimum=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="img/login.jpg" type="image/x-icon">
    <title>Login | Sistema Facturación</title>
</head>
<body>
    <section id="container">
        <form action="" method="post">
            <h3>Iniciar Sesión</h3>
            <img src="img/login.png" alt="" alt="Login">

            <input type="text" name="usuario" placeholder="Usuario" >
            <input type="password" name="clave" placeholder="Contraseña">
            <div class="alert"><?php echo isset($alert)? $alert : ''; ?></div>
            <input type="submit" value="INGRESAR">
        </form>
    </section>
    
</body>
</html>