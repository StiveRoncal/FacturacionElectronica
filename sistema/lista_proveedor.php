<?php 

    session_start();
    if($_SESSION['rol'] != 1 and $_SESSION['rol'] != 2){
        header("Location: ./");
    }
    include "../conexion.php";

?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<?php include "includes/scripts.php";?>
	<title>Lista de Proveedor</title>
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/functions.js"></script>
</head>
<body>
	
	<?php include "includes/header.php";?>
	<section id="container">
        
        <h1><i class="far fa-building"></i> Lista de Proveedores</h1>
        <a href="registro_proveedor.php" class="btn_new"><i class="fas fa-plus"></i> Crear Proveedor</a>

        <form action="buscar_proveedor.php" method="get" class="form_search">
            <input type="text" name="busqueda" id="busqueda" placeholder="Buscar">
            <button type="submit" class="btn_search"><i class="fas fa-search"></i></button>
        </form>

        <div class="containerTable">
        <table>
            <tr>
                <th>ID</th>
                <th>Proveedor</th>
                <th>Contacto</th>
                <th>Teléfono</th>
                <th>Dirección</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
            <?php
            //PAginador
            $sql_register = mysqli_query($conection,"SELECT COUNT(*) as total_registro FROM proveedor WHERE estatus = 1");
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

            $query = mysqli_query($conection,"SELECT * FROM proveedor   
                                                WHERE estatus = 1 ORDER BY codproveedor ASC
                                                LIMIT $desde,$por_pagina    
                                                
                                                ");
            mysqli_close($conection);
            $result = mysqli_num_rows($query);

            if($result > 0)
            {
                while($data = mysqli_fetch_array($query)){
                //VAlidar Formato de fecha hora zonal
                $formato = 'Y-m-d H:i:s';
                $fecha = DateTime::createFromFormat($formato,$data["date_add"]);
             
            ?>

            <tr>
                <td><?php echo $data["codproveedor"]; ?></td>
                <td><?php echo $data["proveedor"]; ?></td>
                <td><?php echo $data["contacto"]; ?></td>
                <td><?php echo $data["telefono"]; ?></td>
                <td><?php echo $data["direccion"]; ?></td>
                <td><?php echo $fecha->format('d-m-Y'); ?></td>
                <td>
                    <a href="editar_proveedor.php?id=<?php echo $data["codproveedor"];?>" class="link_edit"><i class="far fa-edit"></i> Editar</a>
             
                
                    <a href="eliminar_confirmar_proveedor.php?id=<?php echo $data["codproveedor"];?>" class="link_delete"><i class="far fa-trash-alt"></i> Eliminar</a>
                 
                </td>
            </tr>

            <?php 
                   }
                }
            ?>
        </table>
        </div>
        <!--Inicio de Paginador-->
        <div class="paginador">
            <ul>

            <?php
                if($pagina != 1){
            ?>
                <li><a href="?pagina=<?php echo 1; ?>"><i class="fas fa-step-backward"></i></a></li>
                <li><a href="?pagina=<?php echo  $pagina - 1; ?>"><i class="fas fa-backward"></i></a></li>
                <?php 
                }
                    for($i=1;$i<=$total_paginas; $i++){

                        if($i == $pagina){
                            echo '<li class="pageSelected">'.$i.'</li>';
                        }else{

                            echo '<li><a href="?pagina='.$i.'">'.$i.'</a></li>';
                        }
                      
                    }

                    
                    if($pagina != $total_paginas){

                   
                ?>
            
                
                <li><a href="?pagina=<?php echo $pagina + 1; ?>"><i class="fas fa-forward"></i></a></li>
                <li><a href="?pagina=<?php echo $total_paginas; ?>"><i class="fas fa-step-forward"></i></a></li>
                <?php } ?>
            </ul>
        </div>
	</section>

	
	<?php include "includes/footer.php";?>
</body>
</html>