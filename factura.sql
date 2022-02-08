-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-02-2022 a las 00:30:27
-- Versión del servidor: 10.4.22-MariaDB
-- Versión de PHP: 7.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `factura`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `actualizar_precio_producto` (`n_cantidad` INT, `n_precio` DECIMAL(10,2), `codigo` INT)  BEGIN
    	DECLARE nueva_existencia int;
        DECLARE nuevo_total  decimal(10,2);
        DECLARE nuevo_precio decimal(10,2);
        
        DECLARE cant_actual int;
        DECLARE pre_actual decimal(10,2);
        
        DECLARE actual_existencia int;
        DECLARE actual_precio decimal(10,2);
                
        SELECT precio,existencia INTO actual_precio,actual_existencia FROM producto WHERE codproducto = codigo;
        SET nueva_existencia = actual_existencia + n_cantidad;
        SET nuevo_total = (actual_existencia * actual_precio) + (n_cantidad * n_precio);
        SET nuevo_precio = nuevo_total / nueva_existencia;
        
        UPDATE producto SET existencia = nueva_existencia, precio = nuevo_precio WHERE codproducto = codigo;
        
        SELECT nueva_existencia,nuevo_precio;
        
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_detalle_temp` (`codigo` INT, `cantidad` INT, `token_user` VARCHAR(50))  BEGIN
    
    	DECLARE precio_actual decimal(10,2);
        SELECT precio INTO precio_actual FROM producto WHERE codproducto = codigo;
        
        INSERT INTO detalle_temp(token_user,codproducto,cantidad,precio_venta) VALUES(token_user,codigo,cantidad,precio_actual);
        
        SELECT tmp.correlativo, tmp.codproducto, p.descripcion, tmp.cantidad, tmp.precio_venta FROM detalle_temp tmp
        INNER JOIN producto p
        ON tmp.codproducto = p.codproducto
        WHERE tmp.token_user = token_user;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `anular_factura` (`no_factura` INT)  BEGIN
    	DECLARE existe_factura int;
        DECLARE registros int;
        DECLARE a int;
        
        DECLARE cod_producto int;
        DECLARE cant_producto int;
        DECLARE existencia_actual int;
        DECLARE nueva_existencia int;
        
        SET existe_factura = (SELECT COUNT(*) FROM factura WHERE nofactura = no_factura and estatus = 1);
        
        IF existe_factura > 0 THEN
        	CREATE TEMPORARY TABLE  tbl_tmp(
            	id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                cod_prod BIGINT,
                cant_prod  INT);
                
                SET a = 1;
                
                SET registros = (SELECT COUNT(*) FROM detallefactura WHERE nofactura = no_factura);
                
                IF registros > 0 THEN
                	INSERT INTO tbl_tmp(cod_prod,cant_prod) SELECT codproducto, cantidad FROM detallefactura WHERE nofactura = no_factura;
                    WHILE a <= registros DO
                    	SELECT cod_prod,cant_prod INTO cod_producto, cant_producto FROM tbl_tmp WHERE id = a;
                        SELECT existencia INTO existencia_actual FROM producto WHERE codproducto = cod_producto;
                        SET nueva_existencia = existencia_actual + cant_producto;
                        UPDATE producto SET existencia = nueva_existencia WHERE codproducto = cod_producto;
                        
                        SET a=a+1;
                        
                    END WHILE;
                    
                    UPDATE factura SET estatus = 2 WHERE nofactura = no_factura;
                    DROP TABLE tbl_tmp;
                    SELECT * FROM factura WHERE nofactura = no_factura;
                    
                END IF;
        ELSE
        	SELECT 0 factura;
        END IF;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `dataDashboard` ()  BEGIN
   		DECLARE usuarios INT;
        DECLARE clientes INT;
        DECLARE proveedores INT;
        DECLARE productos INT;
        DECLARE ventas INT;
        
        SELECT COUNT(*) INTO usuarios FROM usuario WHERE estatus != 10;
        SELECT COUNT(*) INTO clientes FROM cliente WHERE estatus != 10;
        SELECT COUNT(*) INTO proveedores FROM proveedor WHERE estatus != 10;
        SELECT COUNT(*) INTO productos FROM producto WHERE estatus != 10;
        SELECT COUNT(*) INTO ventas FROM factura WHERE fecha > CURDATE() AND estatus != 10;
        
        SELECT usuarios,clientes,proveedores,productos,ventas;
        
    
	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `del_detalle_temp` (`id_detalle` INT, `token` VARCHAR(50))  BEGIN
    	DELETE FROM detalle_temp WHERE correlativo = id_detalle;
        
        SELECT tmp.correlativo, tmp.codproducto, p.descripcion, tmp.cantidad, tmp.precio_venta FROM detalle_temp tmp
        INNER JOIN producto p
        ON tmp.codproducto = p.codproducto
        WHERE tmp.token_user = token;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `procesar_venta` (`cod_usuario` INT, `cod_cliente` INT, `token` VARCHAR(50))  BEGIN
    	DECLARE factura INT;
        
        DECLARE registros INT;
        DECLARE total DECIMAL(10,2);
        
        DECLARE nueva_existencia int;
        DECLARE existencia_actual int;
        
        DECLARE tmp_cod_producto int;
        DECLARE tmp_cant_producto int;
        DECLARE a INT;
        SET a = 1;
        
        CREATE TEMPORARY TABLE tbl_tmp_tokenuser (
        	id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        	cod_prod BIGINT,
        	cant_prod INT);
            
        SET registros = (SELECT COUNT(*) FROM  detalle_temp WHERE token_user = token);
        
        IF registros > 0 THEN
        	INSERT INTO tbl_tmp_tokenuser(cod_prod,cant_prod) SELECT codproducto,cantidad FROM detalle_temp WHERE token_user = token;
            
            INSERT INTO factura(usuario,codcliente) VALUES(cod_usuario,cod_cliente);
            SET factura = LAST_INSERT_ID();
            
            INSERT INTO detallefactura(nofactura,codproducto,cantidad,precio_venta) SELECT (factura) as nofactura,codproducto,cantidad,precio_venta FROM detalle_temp WHERE token_user = token;
            
            WHILE a <= registros DO
            	SELECT cod_prod,cant_prod INTO tmp_cod_producto, tmp_cant_producto FROM tbl_tmp_tokenuser WHERE id = a;
                SELECT existencia INTO existencia_actual FROM producto WHERE codproducto = tmp_cod_producto;
                
                SET nueva_existencia  = existencia_actual - tmp_cant_producto;
                UPDATE producto SET existencia = nueva_existencia WHERE codproducto = tmp_cod_producto;
                
                SET a=a+1;
            END WHILE;
            
            SET total = (SELECT SUM(cantidad * precio_venta) FROM detalle_temp WHERE token_user = token);
            UPDATE factura SET totalfactura = total WHERE nofactura = factura;
            DELETE FROM detalle_temp WHERE token_user = token;
            TRUNCATE TABLE tbl_tmp_tokenuser;
            SELECT * FROM factura WHERE nofactura = factura;
        ELSE
        	SELECT 0;
        END IF;
    END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `idcliente` int(11) NOT NULL,
  `dni` int(8) DEFAULT NULL,
  `nombre` varchar(80) DEFAULT NULL,
  `telefono` int(11) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `dateadd` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) NOT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`idcliente`, `dni`, `nombre`, `telefono`, `direccion`, `dateadd`, `usuario_id`, `estatus`) VALUES
(1, 0, 'Varios', 0, 'Peru,Pangoa', '2021-09-18 23:23:48', 1, 1),
(2, 7256123, 'Jorge Arias Morales', 945123657, 'Av. España #451 San Fernando', '2021-09-21 19:24:37', 1, 1),
(3, 725601122, 'Jose Mariategui', 985641235, 'Calle 7 de Julio #751', '2021-09-24 20:38:23', 1, 1),
(4, 72560007, 'Stive Esau Roncal Quintimari', 934027842, 'Calle 28 de Julio Nro.725 San Martin de Pangoa', '2022-01-16 17:37:00', 1, 1),
(5, 72560006, 'Katherine Ivonne Roncal Quintimari', 952364851, 'Calle 28 de Julio Nro.725 San Martin de Pangoa', '2022-01-16 17:43:03', 1, 1),
(6, 40526993, 'Esau Edward Roncal Hidalgo', 943932233, 'Av. Pablo Patron 271 BLOCK 271 DPTO. 1 1ER. Pablo 271', '2022-01-22 14:05:37', 1, 1),
(7, 72550686, 'Jelsi Jhosi Roncal Illatopa', 9999, 'Calle 28 de Julio ', '2022-01-22 14:07:11', 1, 1),
(8, 72550685, 'Leslie Karol Roncal Illatopa', 934899355, 'Calle 28 de Julio ', '2022-01-22 14:16:52', 1, 1),
(9, 42080684, 'Efrain David Roncal Hidlago', 925543538, 'Asociación de vivienda Esmeralda San Ramon de Pangoa', '2022-01-24 17:42:45', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` bigint(20) NOT NULL,
  `nit` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `razon_social` varchar(100) NOT NULL,
  `telefono` bigint(20) NOT NULL,
  `email` varchar(200) NOT NULL,
  `direccion` text NOT NULL,
  `iva` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `nit`, `nombre`, `razon_social`, `telefono`, `email`, `direccion`, `iva`) VALUES
(1, '20600164105', 'N&S RONCAL ', 'Ventas y mas....', 943932233, 'nsroncal@gmail.com', 'Calle 28 de Julio #725 San Martin de Pangoa ', '18.00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detallefactura`
--

CREATE TABLE `detallefactura` (
  `correlativo` bigint(11) NOT NULL,
  `nofactura` bigint(11) DEFAULT NULL,
  `codproducto` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `precio_venta` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `detallefactura`
--

INSERT INTO `detallefactura` (`correlativo`, `nofactura`, `codproducto`, `cantidad`, `precio_venta`) VALUES
(1, 1, 3, 1, '190.00'),
(2, 2, 5, 1, '20.00'),
(3, 2, 4, 1, '175.00'),
(5, 3, 1, 3, '4.00'),
(6, 3, 3, 3, '190.00'),
(7, 4, 4, 1, '175.00'),
(8, 5, 3, 1, '190.00'),
(9, 5, 4, 2, '175.00'),
(11, 6, 3, 1, '190.00'),
(12, 7, 3, 1, '190.00'),
(13, 8, 3, 1, '190.00'),
(14, 9, 1, 4, '4.00'),
(15, 10, 4, 6, '175.00'),
(16, 11, 7, 4, '30.00'),
(17, 12, 14, 2, '0.25'),
(18, 12, 5, 2, '20.00'),
(19, 12, 1, 1, '4.50'),
(20, 12, 11, 4, '2.50'),
(21, 12, 12, 2, '24.00'),
(22, 12, 13, 2, '40.00'),
(23, 12, 10, 3, '12.00'),
(24, 13, 5, 1, '20.00'),
(25, 14, 3, 1, '190.00'),
(26, 15, 3, 3, '190.00'),
(27, 16, 15, 1, '6.00'),
(28, 17, 17, 6, '10.00'),
(29, 17, 18, 1, '110.00'),
(30, 17, 1, 1, '4.50'),
(31, 17, 19, 1, '5.00'),
(32, 17, 5, 1, '20.00'),
(33, 17, 20, 8, '3.00'),
(34, 17, 23, 6, '3.00'),
(35, 17, 22, 6, '5.00'),
(36, 17, 21, 1, '2.00'),
(43, 18, 24, 1, '5.00'),
(44, 19, 25, 3, '35.00'),
(45, 20, 1, 2, '4.50'),
(46, 21, 26, 1, '3.50'),
(47, 22, 22, 1, '5.00'),
(48, 22, 28, 10, '3.50'),
(49, 22, 26, 1, '3.50'),
(50, 23, 17, 1, '10.00'),
(51, 24, 23, 3, '3.00'),
(52, 25, 29, 5, '2.00'),
(53, 26, 1, 1, '4.50'),
(54, 26, 30, 4, '1.50'),
(55, 26, 18, 1, '110.00'),
(56, 26, 31, 4, '11.00'),
(60, 27, 29, 8, '2.00'),
(61, 28, 9, 1, '16.00'),
(62, 28, 32, 1, '7.00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_temp`
--

CREATE TABLE `detalle_temp` (
  `correlativo` int(11) NOT NULL,
  `token_user` varchar(50) NOT NULL,
  `codproducto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `detalle_temp`
--

INSERT INTO `detalle_temp` (`correlativo`, `token_user`, `codproducto`, `cantidad`, `precio_venta`) VALUES
(40, 'c81e728d9d4c2f636f067f89cc14862c', 3, 1, '190.00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entradas`
--

CREATE TABLE `entradas` (
  `correlativo` int(11) NOT NULL,
  `codproducto` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `usuario_id` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `entradas`
--

INSERT INTO `entradas` (`correlativo`, `codproducto`, `fecha`, `cantidad`, `precio`, `usuario_id`) VALUES
(1, 1, '2021-09-25 21:43:37', 300, '4.00', 1),
(2, 2, '2021-09-25 21:46:08', 500, '1.00', 3),
(3, 3, '2021-09-26 12:30:36', 5, '190.00', 1),
(4, 4, '2021-09-26 12:35:28', 10, '170.00', 1),
(5, 5, '2021-09-26 15:32:30', 100, '20.00', 1),
(6, 1, '2021-10-01 21:58:18', 200, '4.00', 1),
(7, 1, '2021-10-01 21:58:50', 50, '4.00', 1),
(8, 4, '2021-10-01 23:20:00', 10, '170.00', 1),
(9, 5, '2021-10-01 23:25:32', 5, '20.00', 1),
(10, 5, '2021-10-01 23:33:04', 5, '20.00', 1),
(11, 5, '2021-10-01 23:34:53', 10, '20.00', 1),
(12, 5, '2021-10-01 23:35:09', 20, '20.00', 1),
(13, 3, '2021-10-01 23:35:39', 5, '190.00', 1),
(14, 3, '2021-10-01 23:36:35', 5, '190.00', 1),
(15, 3, '2021-10-01 23:38:22', 5, '190.00', 1),
(16, 3, '2021-10-01 23:39:30', 10, '190.00', 1),
(17, 3, '2021-10-01 23:43:16', 10, '190.00', 1),
(18, 5, '2021-10-02 10:09:18', 20, '10.00', 1),
(19, 3, '2021-10-02 10:32:28', 10, '190.00', 1),
(20, 6, '2022-01-22 12:31:15', 4, '6.00', 1),
(21, 7, '2022-01-23 20:04:21', 4, '30.00', 1),
(22, 8, '2022-01-24 15:22:39', 3, '20.00', 1),
(23, 9, '2022-01-24 15:24:18', 10, '12.00', 1),
(24, 10, '2022-01-24 15:26:08', 10, '12.00', 1),
(25, 11, '2022-01-24 15:26:24', 4, '2.50', 1),
(26, 12, '2022-01-24 15:27:18', 2, '24.00', 1),
(27, 13, '2022-01-24 15:27:43', 2, '40.00', 1),
(28, 14, '2022-01-24 15:28:28', 2, '0.25', 1),
(29, 15, '2022-01-30 09:39:13', 4, '6.00', 1),
(30, 14, '2022-01-30 10:12:06', 30, '0.25', 1),
(31, 16, '2022-01-30 10:40:30', 7, '20.00', 1),
(32, 17, '2022-01-31 08:50:58', 50, '10.00', 1),
(33, 18, '2022-01-31 08:58:04', 7, '110.00', 1),
(34, 19, '2022-01-31 09:49:25', 3, '5.00', 1),
(35, 20, '2022-01-31 09:52:54', 30, '3.00', 1),
(36, 21, '2022-01-31 09:55:22', 28, '2.00', 1),
(37, 22, '2022-01-31 09:59:40', 84, '5.00', 1),
(38, 23, '2022-01-31 10:02:54', 120, '3.00', 1),
(39, 24, '2022-01-31 11:02:19', 14, '5.00', 1),
(40, 25, '2022-01-31 11:03:22', 14, '35.00', 1),
(41, 26, '2022-01-31 12:01:34', 100, '3.50', 1),
(42, 27, '2022-01-31 12:07:11', 200, '0.50', 1),
(43, 28, '2022-01-31 16:03:21', 100, '3.50', 1),
(44, 29, '2022-01-31 18:38:38', 200, '2.00', 1),
(45, 30, '2022-02-01 12:46:52', 100, '1.50', 1),
(46, 31, '2022-02-01 12:49:33', 50, '11.00', 1),
(47, 32, '2022-02-01 19:58:14', 13, '7.00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura`
--

CREATE TABLE `factura` (
  `nofactura` bigint(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario` int(11) DEFAULT NULL,
  `codcliente` int(11) DEFAULT NULL,
  `totalfactura` decimal(10,2) DEFAULT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `factura`
--

INSERT INTO `factura` (`nofactura`, `fecha`, `usuario`, `codcliente`, `totalfactura`, `estatus`) VALUES
(1, '2022-01-21 23:05:55', 1, 5, '190.00', 2),
(2, '2022-01-21 23:59:27', 1, 5, '195.00', 1),
(3, '2022-01-22 00:04:22', 1, 1, '582.00', 1),
(4, '2022-01-22 10:11:39', 1, 1, '175.00', 1),
(5, '2022-01-22 10:44:52', 1, 5, '540.00', 2),
(6, '2022-01-22 10:46:12', 1, 5, '190.00', 1),
(7, '2022-01-22 11:02:39', 1, 5, '190.00', 2),
(8, '2022-01-22 11:25:55', 1, 5, '190.00', 2),
(9, '2022-01-22 17:45:30', 1, 1, '16.00', 2),
(10, '2022-01-22 20:45:06', 1, 1, '1050.00', 2),
(11, '2022-01-23 20:06:10', 1, 1, '120.00', 1),
(12, '2022-01-24 15:31:17', 1, 1, '219.00', 1),
(13, '2022-01-24 15:39:34', 1, 1, '20.00', 1),
(14, '2022-01-28 16:12:42', 1, 1, '190.00', 1),
(15, '2022-01-28 16:24:27', 1, 1, '570.00', 1),
(16, '2022-01-30 09:39:37', 1, 1, '6.00', 1),
(17, '2022-01-31 10:04:50', 1, 1, '273.50', 1),
(18, '2022-01-31 11:03:36', 1, 1, '5.00', 1),
(19, '2022-01-31 11:03:59', 1, 1, '105.00', 1),
(20, '2022-01-31 12:01:45', 1, 1, '9.00', 1),
(21, '2022-01-31 12:01:52', 1, 1, '3.50', 1),
(22, '2022-01-31 16:03:43', 1, 1, '43.50', 1),
(23, '2022-01-31 16:04:14', 1, 1, '10.00', 1),
(24, '2022-01-31 16:07:08', 1, 1, '9.00', 1),
(25, '2022-01-31 18:39:20', 1, 1, '10.00', 1),
(26, '2022-02-01 12:49:47', 1, 1, '164.50', 1),
(27, '2022-02-01 17:15:42', 1, 1, '16.00', 1),
(28, '2022-02-01 19:58:26', 1, 1, '23.00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `codproducto` int(11) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `proveedor` int(11) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `existencia` int(11) DEFAULT NULL,
  `date_add` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) NOT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1,
  `foto` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`codproducto`, `descripcion`, `proveedor`, `precio`, `existencia`, `date_add`, `usuario_id`, `estatus`, `foto`) VALUES
(1, 'Cinta Aislante 3M', 1, '4.50', 543, '2021-09-25 21:43:37', 1, 1, 'img_94e054e0cd3fc1cdda5a4af6e4aa4600jpg'),
(2, 'Enchufe Simple ', 2, '1.00', 500, '2021-09-25 21:46:08', 3, 1, 'img_f2afaac85dcdfa6340d4bd3b4ddbaf1djpg'),
(3, 'Cable Mellizo 2x16', 3, '190.00', 44, '2021-09-26 12:30:36', 1, 1, 'img_dd634cb56bc23ce0901f7acb901baf07jpg'),
(4, 'Cable Solido Indeco #14 de 7 Hilos', 1, '175.00', 16, '2021-09-26 12:35:28', 1, 1, 'img_e9b6bf6a90401d6c8a99c3ec0de450ebjpg'),
(5, 'LLave Ternomagnetica', 1, '20.00', 157, '2021-09-26 15:32:30', 1, 1, 'img_0cba7b6835917b9c3f1b11105a82d81ajpg'),
(6, 'Grapa de Concreto Schubert de 10mm ', 3, '6.00', 4, '2022-01-22 12:31:15', 1, 1, 'img_322cc770e7792cb852bbe73ef42121a3jpg'),
(7, 'Galón de Pisco San Vicente Destilado de Uva Quebranta', 2, '30.00', 0, '2022-01-23 20:04:21', 1, 1, 'img_producto.png'),
(8, 'Interruptor Termomagnético 2x32Am', 3, '20.00', 3, '2022-01-24 15:22:39', 1, 1, 'img_producto.png'),
(9, 'Toma Corriente Bticino Visible Triple', 3, '16.00', 9, '2022-01-24 15:24:18', 1, 1, 'img_f7bc0d32b3c28b1300f5014460be00f8jpg'),
(10, 'Toma Corriente Bticino Empotrado Doble con Tierra', 3, '12.00', 10, '2022-01-24 15:26:08', 1, 1, 'img_e3ba8937d1327243bb58337126eea334jpg'),
(11, 'Caja Electrica Octagonal Pavco ', 3, '2.50', 4, '2022-01-24 15:26:24', 1, 1, 'img_b0e82d15970353d515ec25baa2157f1ejpg'),
(12, 'Foco Led Modelo Botella Phelix LCB120-38', 3, '24.00', 2, '2022-01-24 15:27:18', 1, 1, 'img_1eed41375edaa0aad7262e0fded8046fjpg'),
(13, 'Bracket de Tubo florescente Led de Dos Lineas', 3, '40.00', 2, '2022-01-24 15:27:43', 1, 1, 'img_producto.png'),
(14, 'Unidad de Cintillo nyloz', 3, '0.25', 32, '2022-01-24 15:28:28', 1, 1, 'img_producto.png'),
(15, 'Wailec Electrical Wall Socket Plano', 2, '6.00', 3, '2022-01-30 09:39:13', 1, 1, 'img_producto.png'),
(16, 'Extension Universal Amarillo de 10mts 2x16', 3, '20.00', 7, '2022-01-30 10:40:30', 1, 1, 'img_d1e9d40e9b2567b958a4cc7a21ec55ffjpg'),
(17, 'Foco Led Modelo Botella LCB70-12W Phelix Led Premium Series', 3, '10.00', 43, '2022-01-31 08:50:58', 1, 1, 'img_14b906bf2b00947336e1db0c164f3290jpg'),
(18, 'Rollo Cable Celec Nro.14 ', 3, '110.00', 5, '2022-01-31 08:58:04', 1, 1, 'img_747bc40c040961b5c5189cd05091ac7fjpg'),
(19, 'Spray amarillo', 3, '5.00', 2, '2022-01-31 09:49:25', 1, 1, 'img_producto.png'),
(20, 'WALL SOCKET PLANO BLANCO ', 3, '3.00', 22, '2022-01-31 09:52:54', 1, 1, 'img_75e6497d107032ea738447fdf02aea6fjpg'),
(21, 'Caja Piramide Sin Tapa', 3, '2.00', 27, '2022-01-31 09:55:22', 1, 1, 'img_745e7c02dc1b9487f5854ebca0b05ab4jpg'),
(22, 'Toma Corriente III Visible Celux', 3, '5.00', 77, '2022-01-31 09:59:40', 1, 1, 'img_f3a7ca382e6982b7e2940ed8d6a92395jpg'),
(23, 'Interruptor simple Visible Wailec', 3, '3.00', 111, '2022-01-31 10:02:54', 1, 1, 'img_1cefd1e9c4fdd42915a2ec22e7246c46jpg'),
(24, 'Grapa de Concreto de 8mm ', 3, '5.00', 13, '2022-01-31 11:02:19', 1, 1, 'img_f440b129b1bbdcd7ed7faed0fc8f3fd0jpg'),
(25, 'caja porta medidor monofásico Azul', 3, '35.00', 11, '2022-01-31 11:03:22', 1, 1, 'img_541ba33e5f445b2b09b45d5dbb78597fjpg'),
(26, 'Enchufe Vision Electric Plano', 3, '3.50', 98, '2022-01-31 12:01:34', 1, 1, 'img_07cb684a5bd76fe19b211b42f4e7e20cjpg'),
(27, 'Codo de Luz de 3/4', 3, '0.50', 200, '2022-01-31 12:07:11', 1, 1, 'img_745a1bad753b5d9b748b3b117d39b341jpg'),
(28, 'Mtr Cable Mellizo 2X14 Brande', 3, '3.50', 90, '2022-01-31 16:03:21', 1, 1, 'img_479927984449f1058fad3d88ae5daf82jpg'),
(29, 'Canaleta 10x15', 3, '2.00', 187, '2022-01-31 18:38:38', 1, 1, 'img_7a16d33c1614ba25cd0a9f3e7e2beba6jpg'),
(30, 'Sockete Colgate Blanco', 3, '1.50', 96, '2022-02-01 12:46:52', 1, 1, 'img_9920858900790cc4e4e50da128ce0321jpg'),
(31, 'Foco Led Modelo Botella LCB80-18', 3, '12.00', 46, '2022-02-01 12:49:33', 1, 1, 'img_f31c75f53484190ef6592ebc0df5d89ajpg'),
(32, 'Interruptor Simple Visible Bticino ', 3, '7.00', 12, '2022-02-01 19:58:14', 1, 1, 'img_3f8a150dac2d0fb9e205462c430f8245jpg');

--
-- Disparadores `producto`
--
DELIMITER $$
CREATE TRIGGER `entradas_A_I` AFTER INSERT ON `producto` FOR EACH ROW BEGIN
    	INSERT INTO entradas(codproducto,cantidad,precio,usuario_id)
        VALUES(new.codproducto,new.existencia,new.precio,new.usuario_id);
    END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `codproveedor` int(11) NOT NULL,
  `proveedor` varchar(100) NOT NULL,
  `contacto` varchar(100) NOT NULL,
  `telefono` bigint(11) NOT NULL,
  `direccion` text NOT NULL,
  `date_add` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) NOT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `proveedor`
--

INSERT INTO `proveedor` (`codproveedor`, `proveedor`, `contacto`, `telefono`, `direccion`, `date_add`, `usuario_id`, `estatus`) VALUES
(1, 'Teve Cable Peru sac\r\n', 'Jorge Arias Morelaes', 945123457, 'Calle 7 de Julio #751', '2021-09-25 18:17:18', 1, 1),
(2, 'IBMS', 'Jose de la Torre', 945124785, 'Av. Las palmeras  s/n', '2021-09-25 19:43:46', 1, 1),
(3, 'Cable Cable Pangoa', 'John Peñaloza Quintanilla', 945216589, 'Av. Espana #431', '2021-09-26 11:19:35', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `idrol` int(11) NOT NULL,
  `rol` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`idrol`, `rol`) VALUES
(1, 'Administrador'),
(2, 'Supervisor'),
(3, 'Vendedor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `idusuario` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `usuario` varchar(15) DEFAULT NULL,
  `clave` varchar(100) DEFAULT NULL,
  `rol` int(11) DEFAULT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`idusuario`, `nombre`, `correo`, `usuario`, `clave`, `rol`, `estatus`) VALUES
(1, 'Stive Esau Roncal Quintimari', 'stiveroncal@gmail.com', 'admin', '25f9e794323b453885f5181f1b624d0b', 1, 1),
(2, 'Katherine Roncal Quintimari', 'katherine_Roncal@gmail.com', 'Katherine', '202cb962ac59075b964b07152d234b70', 3, 1),
(3, 'Esau  Edward Roncal Hildalgo ', 'esaroncal@gmail.com', 'Esau', '202cb962ac59075b964b07152d234b70', 1, 1),
(4, 'Gol D Roger', 'roger@gmail.com', 'roger', 'e81502a921e78c4ddb017a555586664c', 2, 1),
(5, 'Genaro Alexis Fernandez Roncal', 'alexisfernandez9988@gmail', 'Alexis', '3cd13a277fbc2fea5ef64364c8b6f853', 2, 1),
(6, 'Jose D San martin', 'josedesanmartin@gmail.com', 'jose', '202cb962ac59075b964b07152d234b70', 2, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`idcliente`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detallefactura`
--
ALTER TABLE `detallefactura`
  ADD PRIMARY KEY (`correlativo`),
  ADD KEY `codproducto` (`codproducto`),
  ADD KEY `nofactura` (`nofactura`);

--
-- Indices de la tabla `detalle_temp`
--
ALTER TABLE `detalle_temp`
  ADD PRIMARY KEY (`correlativo`),
  ADD KEY `nofactura` (`token_user`),
  ADD KEY `codproducto` (`codproducto`);

--
-- Indices de la tabla `entradas`
--
ALTER TABLE `entradas`
  ADD PRIMARY KEY (`correlativo`),
  ADD KEY `codproducto` (`codproducto`);

--
-- Indices de la tabla `factura`
--
ALTER TABLE `factura`
  ADD PRIMARY KEY (`nofactura`),
  ADD KEY `usuario` (`usuario`),
  ADD KEY `codcliente` (`codcliente`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`codproducto`),
  ADD KEY `proveedor` (`proveedor`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`codproveedor`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`idrol`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`idusuario`),
  ADD KEY `rol` (`rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `idcliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `detallefactura`
--
ALTER TABLE `detallefactura`
  MODIFY `correlativo` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de la tabla `detalle_temp`
--
ALTER TABLE `detalle_temp`
  MODIFY `correlativo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT de la tabla `entradas`
--
ALTER TABLE `entradas`
  MODIFY `correlativo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de la tabla `factura`
--
ALTER TABLE `factura`
  MODIFY `nofactura` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `codproducto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  MODIFY `codproveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `idrol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `idusuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `cliente_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`idusuario`);

--
-- Filtros para la tabla `detallefactura`
--
ALTER TABLE `detallefactura`
  ADD CONSTRAINT `detallefactura_ibfk_1` FOREIGN KEY (`nofactura`) REFERENCES `factura` (`nofactura`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detallefactura_ibfk_2` FOREIGN KEY (`codproducto`) REFERENCES `producto` (`codproducto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_temp`
--
ALTER TABLE `detalle_temp`
  ADD CONSTRAINT `detalle_temp_ibfk_2` FOREIGN KEY (`codproducto`) REFERENCES `producto` (`codproducto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `entradas`
--
ALTER TABLE `entradas`
  ADD CONSTRAINT `entradas_ibfk_1` FOREIGN KEY (`codproducto`) REFERENCES `producto` (`codproducto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `factura`
--
ALTER TABLE `factura`
  ADD CONSTRAINT `factura_ibfk_1` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `factura_ibfk_2` FOREIGN KEY (`codcliente`) REFERENCES `cliente` (`idcliente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`proveedor`) REFERENCES `proveedor` (`codproveedor`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `producto_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD CONSTRAINT `proveedor_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`rol`) REFERENCES `rol` (`idrol`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
