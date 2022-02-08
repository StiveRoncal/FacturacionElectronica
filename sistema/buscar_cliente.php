<?php 
     session_start();
    
    
    include "../conexion.php";

?>

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<?php include "includes/scripts.php";?>
	<title>Lista de Clientes</title>
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/functions.js"></script>
</head>
<body>
	
	<?php include "includes/header.php";?>
	<section id="container">
        <?php

            $busqueda = strtolower($_REQUEST['busqueda']);
            if(empty($busqueda)){
                header("Location: lista_clientes.php");
            }
        ?>
        
        <h1><i class="fas fa-user"></i> Lista de Clientes</h1>
        <a href="registrar_cliente.php" class="btn_new"><i class="fas fa-user-plus"></i> Crear Cliente</a>

        <form action="buscar_cliente.php" method="get" class="form_search">
            <input type="text" name="busqueda" id="busqueda" placeholder="Buscar" value="<?php echo $busqueda;?>">
            <button type="submit" class="btn_search"><i class="fas fa-search"></i></button>
        </form>
        <div class="containerTable">
        <table>
            <tr>
            <th>ID</th>
                <th>Nombre</th>
                <th>DNI</th>
                <th>Teléfono</th>
                <th>Dirección</th>
                <th>Acciones</th>
            </tr>
            <?php
            //PAginador
            

            $sql_register = mysqli_query($conection,"SELECT COUNT(*) as total_registro FROM cliente
                                                     WHERE ( idcliente LIKE '%$busqueda%' OR
                                                             dni       LIKE '%$busqueda%' OR
                                                             nombre    LIKE '%$busqueda%' OR
                                                             telefono  LIKE '%$busqueda%' OR
                                                             direccion LIKE '%$busqueda%' )                        
                                                             AND  estatus = 1");

            $result_register = mysqli_fetch_array($sql_register);
            $total_registro = $result_register['total_registro'];

            $por_pagina = 10;

            if(empty($_GET['pagina'])){
                $pagina = 1;
            }else{
                $pagina = $_GET['pagina'];
            }

            $desde = ($pagina - 1) * $por_pagina;
            $total_paginas = ceil($total_registro / $por_pagina); 

            $query = mysqli_query($conection,"SELECT * FROM cliente 
                                                
                                                WHERE
                                                ( idcliente LIKE '%$busqueda%' OR
                                                  dni  LIKE '%$busqueda%' OR
                                                  nombre  LIKE '%$busqueda%' OR
                                                  telefono  LIKE '%$busqueda%' OR
                                                  direccion LIKE '%$busqueda%' 
                                                 ) 
                                                AND 
                                                estatus = 1 ORDER BY idcliente ASC
                                                LIMIT $desde,$por_pagina    
                                                
                                                ");
            mysqli_close($conection);
            $result = mysqli_num_rows($query);

            if($result > 0)
            {
                while($data = mysqli_fetch_array($query)){

             
            ?>

            <tr>
                <td><?php echo $data["idcliente"]; ?></td>
                <td><?php echo $data["dni"]; ?></td>
                <td><?php echo $data["nombre"]; ?></td>
                <td><?php echo $data["telefono"]; ?></td>
                <td><?php echo $data["direccion"]; ?></td>
                <td>
                    <a href="editar_clientes.php?id=<?php echo $data["idcliente"];?>" class="link_edit"><i class="far fa-edit"></i> Editar</a>
                    <?php if($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2){?>  
                    |
                    <a href="eliminar_confirmar_cliente.php?id=<?php echo $data["idcliente"];?>" class="link_delete"><i class="far fa-trash-alt"></i> Eliminar</a>
                    <?php } ?>
                </td>
            </tr>

            <?php 
                   }
                }
            ?>
        </table>
        </div>
        <?php
            if($total_registro != 0){

         
        ?>
        <!--Inicio de Paginador-->
        <div class="paginador">
            <ul>

            <?php
                if($pagina != 1){
            ?>
                <li><a href="?pagina=<?php echo 1; ?>&busqueda=<?php $busqueda;?>"><i class="fas fa-step-backward"></i></a></li>
                <li><a href="?pagina=<?php echo  $pagina - 1; ?>&busqueda=<?php $busqueda;?>"><i class="fas fa-backward"></i></a></li>
                <?php 
                }
                    for($i=1;$i<=$total_paginas; $i++){

                        if($i == $pagina){
                            echo '<li class="pageSelected">'.$i.'</li>';
                        }else{

                            echo '<li><a href="?pagina='.$i.'&busqueda='.$busqueda.'">'.$i.'</a></li>';
                        }
                      
                    }

                    
                    if($pagina != $total_paginas){

                   
                ?>
            
                
                <li><a href="?pagina=<?php echo $pagina + 1; ?>&busqueda=<?php $busqueda;?>"><i class="fas fa-forward"></i></a></li>
                <li><a href="?pagina=<?php echo $total_paginas; ?>&busqueda=<?php $busqueda;?>"><i class="fas fa-step-forward"></i></a></li>
                <?php } ?>
            </ul>
        </div>
        
	</section>
<?php }?>
	
	<?php include "includes/footer.php";?>
</body>
</html>