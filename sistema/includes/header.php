<?php 

//Validacion de url de salir


if(empty($_SESSION['active'])){
    header('Location: ../');
}

?>
<header>
		<script src="js/jquery.min.js"></script>
		<div class="header">
			<a href="#" class="btnMenu"><i class="fas fa-bars"></i></a>
			<h1>Sistema Facturación</h1>
			<div class="optionsBar">
				<!-- <p>San Martin de Pangoa, <?php echo fechaC(); ?></p> -->
				<span>|</span>
				<span class="user"><?php echo  $_SESSION['user'];?></span>
				<img class="photouser" src="img/user.png" alt="Usuario">
				<a href="salir.php"><img class="close" src="img/salir.png" alt="Salir del sistema" title="Salir"></a>
			</div>
		</div>
		<?php include "nav.php"?>
	</header>

	<div class="modal">
		<div class="bodyModal">
			
		</div>
	</div>