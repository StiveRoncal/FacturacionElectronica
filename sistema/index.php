<?php
	session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<?php include "includes/scripts.php";?>
	<title>Sistema Ventas</title>
	<script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/functions.js"></script>
</head>
<body>
	
	<?php 
		
		include "includes/header.php";
		include "../conexion.php";


		// Datos de empresa
		$nit = '';
		$nombreEmpresa = '';
		$razonSocial = '';
		$telEmpresa = '';
		$emailEmpresa = '';
		$dirEmpresa = '';
		$iva = '';

		$query_empresa = mysqli_query($conection,"SELECT * FROM configuracion");
		$row_empresa = mysqli_num_rows($query_empresa);
		if($row_empresa > 0){
			while($arrInfoEmpresa = mysqli_fetch_assoc($query_empresa)){
				$nit = $arrInfoEmpresa['nit'];
				$nombreEmpresa = $arrInfoEmpresa['nombre'];
				$razonSocial = $arrInfoEmpresa['razon_social'];
				$telEmpresa = $arrInfoEmpresa['telefono'];
				$emailEmpresa = $arrInfoEmpresa['email'];
				$dirEmpresa = $arrInfoEmpresa['direccion'];
				$iva = $arrInfoEmpresa['iva'];
			}
		}


		// Llamar procedimietno almacenado para numero de contador de usuarios, clientem ventas 
		$query_dash = mysqli_query($conection,"CALL dataDashboard();");
		$result_dash = mysqli_num_rows($query_dash);
		if($result_dash > 0){
			$data_dash = mysqli_fetch_assoc($query_dash);
			mysqli_close($conection);
		}


		?>
	<section id="container">
		<div class="divContainer">
			<div>
				<h1 class="titlePanelControl">Panel de Control</h1>
			</div>

			<!-- Ventana de barra de acciones -->
			<div class="dashboard">
				<?php  if($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2){?>
				<!-- Cantidad de usuario -->
				<a href="lista_usuarios.php">
					<i class="fas fa-users"></i>
					<p>
						<strong>Usuarios</strong><br>
						<span><?= $data_dash['usuarios'];?></span>
					</p>
				</a>
				<?php } ?>

				<!-- Cantidad de clientes -->
				<a href="lista_clientes.php">
					<i class="fas fa-user"></i>
					<p>
						<strong>Clientes</strong><br>
						<span><?= $data_dash['clientes'];?></span>
					</p>
				</a>
				<?php  if($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2){?>

				<!-- Cantidad de proveedores -->
				<a href="lista_proveedor.php">
					<i class="far fa-building"></i>
					<p>
						<strong>Proveedores</strong><br>
						<span><?= $data_dash['proveedores'];?></span>
					</p>
				</a>
				<?php } ?>
				<!-- Cantidad de Productos -->
				<a href="lista_productos.php">
					<i class="fas fa-cubes"></i>
					<p>
						<strong>Productos</strong><br>
						<span><?= $data_dash['productos'];?></span>
					</p>
				</a>

				<!-- Cantidad de Ventas -->
				<a href="lista_clientes.php">
					<i class="far fa-file-alt"></i>
					<p>
						<strong>Ventas</strong><br>
						<span><?= $data_dash['ventas'];?></span>
					</p>
				</a>
			</div>
		</div>
			<!-- informacion del sistema -->
			<div class="divInfoSistema">
				<div>
					<h1 class="titlePanelControl">Configuración</h1>
				</div>
				<div class="containerPerfil">

					<div class="containerDataUser">
						<div class="logoUser">
							<img src="img/logoUser.png" alt="">
						</div>
						<div class="divDataUser">
							<h4>Información Personal</h4>

							<div>
								<label for="">Nombre:</label> <span><?= $_SESSION['nombre'] ?></span>
 							</div>
							 <div>
								<label for="">Correo:</label> <span><?= $_SESSION['email'] ?></span>
 							</div>

							 <h4>Datos Usuario</h4>
							 <div>
								<label for="">Rol:</label> <span><?= $_SESSION['rol_name'] ?></span>
 							</div>
							 <div>
								<label for="">Usuario:</label> <span><?= $_SESSION['user'] ?></span>
 							</div>

							 <h4>Cambiar Contraseña</h4>
							 
							 <form action="" method="post" name="frmChangePass" id="frmChangePass">
								 <div>
									 <input type="password" name="txtPassUser" id="txtPassUser" placeholder="Contraseña Actual" required>
								 </div>
								 <div>
									 <input class="newPass"type="password" name="txtNewPassUser" id="txtNewPassUser" placeholder="Nueva Contraseña" required>
								 </div>
								 <div>
									 <input class="newPass" type="password" name="txtPassConfirm" id="txtPassConfirm" placeholder="Confirmar Contraseña" required>
								 </div>
								 <div class="alertChangesPass" style="display: none;">

								 </div>
								 <div>
									 <button type="submit" class="btn_save btnChangePass"><i class="fas fa-key"></i> Cambiar Contraseña</button>
								 </div>
							 </form>
						</div>
					</div>

					<?php  if($_SESSION['rol'] == 1){?>
					<div class="containerDataEmpresa">
						<div class="logoEmpresa">
							<img src="img/logoEmpresa.png" alt="">
						</div>
						<h4>Datos de La Empresa</h4>
						<form action="" method="post" name="frmEmpresa" id="frmEmpresa">
							<input type="hidden" name="action" value="updateDataEmpresa">
							<div>
								<label for="">Ruc:</label><input type="text" name="txtRuc" id="txtRuc" placeholder="Ruc De la Empresa" value="<?= $nit;?>" required>
							</div>
							<div>
								<label for="">Nombre: </label> <input type="text" name="txtNombre" id="txtNombre" placeholder="Nombre de la Empresa" value="<?= $nombreEmpresa;?>" required>
							</div>
							<div>
								<label for="">Razon Social: </label><input type="text" name="txtRSocial" id="txtRSocial" placeholder="Razon Social" value="<?= $razonSocial;?>">
							</div>
							<div>
								<label for="">Teléfono:</label><input type="text" name="txtTelEmpresa" id="txtTelEmpresa" placeholder="Numero de Telefono" value="<?= $telEmpresa;?>" required>
							</div>
							<div>
								<label for="">Correo Electrónico:</label> <input type="email" name="txtEmailEmpresa" id="txtEmailEmpresa" placeholder="Correo Electrónico" value="<?= $emailEmpresa;?>">
							</div>
							<div>
								<label for="">Dirección:</label> <input type="text" name="txtDirEmpresa" id="txtDirEmpresa" placeholder="Correo Electrónico" value="<?= $dirEmpresa;?>" required>
							</div>
							<div>
								<label for=""> IGV (%):</label><input type="text" name="txtIgv" id="txtIgv" placeholder="Impuesto al valor agregado (IGV)" value="<?= $iva;?>" required>
							</div>

							<div class="alertFormEmpresa" style="display:none;"></div>

							<div>
								<button type="submit" class="btn_save btnChangePass"><i class="far fa-save fa-lg"></i> Guardar Datos</button>
							</div>

							
						</form>
					</div>
					<?php }?>
				</div>
			</div>
		
	</section>

	
	<?php include "includes/footer.php";?>
</body>
</html>