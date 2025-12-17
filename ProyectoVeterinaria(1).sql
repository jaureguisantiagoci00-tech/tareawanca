-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 10-12-2025 a las 10:22:57
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ProyectoVeterinaria`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) UNSIGNED NOT NULL,
  `servicio` varchar(50) NOT NULL,
  `fecha` date NOT NULL,
  `hora` varchar(10) DEFAULT NULL,
  `tipo_servicio` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `estado` enum('pendiente','confirmada','completada','cancelada') DEFAULT 'pendiente',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_backups`
--

CREATE TABLE `configuracion_backups` (
  `id` int(11) NOT NULL,
  `archivo` varchar(255) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `tamanio` int(11) DEFAULT NULL,
  `usuario_id` int(10) UNSIGNED DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_sistema`
--

CREATE TABLE `configuracion_sistema` (
  `id` int(11) NOT NULL,
  `clave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT 'general',
  `tipo` varchar(20) DEFAULT 'text',
  `opciones` text DEFAULT NULL,
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion_sistema`
--

INSERT INTO `configuracion_sistema` (`id`, `clave`, `valor`, `descripcion`, `categoria`, `tipo`, `opciones`, `actualizado_en`, `creado_en`) VALUES
(1, 'sitio_nombre', 'Veterinaria PetCar', 'Nombre del negocio', 'general', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(2, 'sitio_email', 'info@veterinaria.com', 'Email de contacto', 'general', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(3, 'sitio_telefono', '+51 123 456 789', 'Teléfono principal', 'general', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(4, 'sitio_direccion', 'Av. Principal 123, Lima', 'Dirección del local', 'general', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(5, 'sitio_facebook', 'https://facebook.com/veterinaria', 'Facebook', 'general', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(6, 'sitio_instagram', 'https://instagram.com/veterinaria', 'Instagram', 'general', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(7, 'inventario_stock_minimo_default', '10', 'Stock mínimo por defecto', 'inventario', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(8, 'inventario_alertas_email', '1', 'Enviar alertas por email', 'inventario', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(9, 'inventario_notificar_bajo_stock', '1', 'Notificar bajo stock', 'inventario', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(10, 'ventas_igv', '18', 'Porcentaje de IGV', 'ventas', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(11, 'ventas_moneda', 'PEN', 'Símbolo de moneda', 'ventas', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(12, 'ventas_moneda_simbolo', 'S/', 'Símbolo de moneda', 'ventas', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(13, 'ventas_numero_serie', 'F001', 'Número de serie para facturas', 'ventas', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(14, 'ventas_numero_inicial', '1', 'Número inicial para facturas', 'ventas', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(15, 'citas_horario_inicio', '08:00', 'Hora de inicio de atención', 'citas', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(16, 'citas_horario_fin', '18:00', 'Hora de fin de atención', 'citas', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(17, 'citas_duracion_default', '30', 'Duración por defecto (minutos)', 'citas', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(18, 'citas_max_diarias', '20', 'Máximo de citas por día', 'citas', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(19, 'smtp_host', 'smtp.gmail.com', 'Servidor SMTP', 'correo', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(20, 'smtp_port', '587', 'Puerto SMTP', 'correo', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(21, 'smtp_usuario', 'tuemail@gmail.com', 'Usuario SMTP', 'correo', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(22, 'smtp_password', '', 'Contraseña SMTP', 'correo', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(23, 'smtp_encryption', 'tls', 'Tipo de encriptación', 'correo', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(24, 'backup_automatico', '0', 'Backup automático', 'backup', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(25, 'backup_frecuencia', 'daily', 'Frecuencia de backup', 'backup', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(26, 'backup_guardar_dias', '30', 'Días a guardar backups', 'backup', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(27, 'seguridad_intentos_login', '3', 'Intentos máximos de login', 'seguridad', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(28, 'seguridad_bloqueo_minutos', '15', 'Minutos de bloqueo', 'seguridad', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(29, 'seguridad_requerir_2fa', '0', 'Requerir autenticación en dos pasos', 'seguridad', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(30, 'tema_color_primario', '#4361ee', 'Color primario', 'apariencia', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(31, 'tema_modo_oscuro', '0', 'Modo oscuro', 'apariencia', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43'),
(32, 'tema_logo_url', '', 'URL del logo', 'apariencia', 'text', NULL, '2025-12-10 09:20:00', '2025-12-10 09:19:43');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas_externas`
--

CREATE TABLE `facturas_externas` (
  `id` int(11) NOT NULL,
  `proveedor` varchar(100) NOT NULL,
  `numero_factura` varchar(50) NOT NULL,
  `fecha_emision` date NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `tipo_gasto` varchar(50) NOT NULL,
  `archivo` varchar(255) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `subido_por` int(10) UNSIGNED DEFAULT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_stock`
--

CREATE TABLE `movimientos_stock` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `tipo` enum('ENTRADA','SALIDA','AJUSTE') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `usuario` varchar(100) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `categoria` varchar(80) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `stock_minimo` int(11) DEFAULT 10,
  `costo` decimal(10,2) DEFAULT 0.00,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `categoria`, `descripcion`, `precio`, `imagen`, `stock_quantity`, `stock_minimo`, `costo`, `fecha_actualizacion`) VALUES
(1, 'Dog Chow Adulto Pollo 3KG', 'alimento', NULL, 26.90, 'dogchow_adulto_pollo_3kg.jpg', 100, 10, 18.83, '2025-12-10 01:22:08'),
(2, 'Dog Chow Adulto Res 3KG', 'alimento', NULL, 27.50, 'dogchow_adulto_res_3kg.jpg', 100, 10, 19.25, '2025-12-10 01:22:08'),
(3, 'Dog Chow Cachorro 2KG', 'alimento', NULL, 22.90, 'dogchow_cachorro_2kg.jpg', 100, 10, 16.03, '2025-12-10 01:22:08'),
(4, 'Ricocan Pollo 1.5KG', 'alimento', NULL, 12.90, 'ricocan_pollo_1_5kg.jpg', 100, 10, 9.03, '2025-12-10 01:22:08'),
(5, 'Ricocan Cordero 3KG', 'alimento', NULL, 23.90, 'ricocan_cordero_3kg.jpg', 100, 10, 16.73, '2025-12-10 01:22:08'),
(6, 'Ricocan Carne 1.5KG', 'alimento', NULL, 13.50, 'ricocan_carne_1_5kg.jpg', 100, 10, 9.45, '2025-12-10 01:22:08'),
(7, 'RicoCat Pescado 1KG', 'alimento', NULL, 11.90, 'ricocat_pescado_1kg.jpg', 100, 10, 8.33, '2025-12-10 01:22:08'),
(8, 'RicoCat Carne 1KG', 'alimento', NULL, 12.50, 'ricocat_carne_1kg.jpg', 100, 10, 8.75, '2025-12-10 01:22:08'),
(9, 'RicoCat Mix Gourmet 2.5KG', 'alimento', NULL, 24.90, 'ricocat_mix_2_5kg.jpg', 100, 10, 17.43, '2025-12-10 01:22:08'),
(10, 'Whiskas Adulto Pollo 1.5KG', 'alimento', NULL, 21.90, 'whiskas_pollo_1_5kg.jpg', 100, 10, 15.33, '2025-12-10 01:22:08'),
(11, 'Whiskas Adulto Carne 1.5KG', 'alimento', NULL, 22.50, 'whiskas_carne_1_5kg.jpg', 100, 10, 15.75, '2025-12-10 01:22:08'),
(12, 'Gati Adulto Pollo 1KG', 'alimento', NULL, 10.00, 'gati_pollo_1kg.jpg', 100, 10, 7.00, '2025-12-10 01:22:08'),
(13, 'Gati Adulto Carne 1KG', 'alimento', NULL, 10.50, 'gati_carne_1kg.jpg', 100, 10, 7.35, '2025-12-10 01:22:08'),
(14, 'Doguitos Tiras de Pollo 70g', 'snack', NULL, 9.50, 'doguitos_pollo_70g.jpg', 100, 10, 6.65, '2025-12-10 01:22:08'),
(15, 'Whiskas Temptations Pollo 85g', 'snack', NULL, 10.90, 'whiskas_temptations_pollo_85g.jpg', 100, 10, 7.63, '2025-12-10 01:22:08'),
(16, 'Whiskas Temptations Atún 85g', 'snack', NULL, 11.50, 'whiskas_temptations_atun_85g.jpg', 100, 10, 8.05, '2025-12-10 01:22:08'),
(17, 'Gotas Antipulgas para Perros', 'medicina', NULL, 29.90, 'gotas_antipulgas_perro.jpg', 100, 10, 20.93, '2025-12-10 01:22:08'),
(18, 'Gotas Antipulgas para Gatos', 'medicina', NULL, 28.50, 'gotas_antipulgas_gato.jpg', 100, 10, 19.95, '2025-12-10 01:22:08'),
(19, 'Desparasitante Canino 10kg', 'medicina', NULL, 19.90, 'desparasitante_canino_10kg.jpg', 100, 10, 13.93, '2025-12-10 01:22:08'),
(20, 'Desparasitante Felino 5kg', 'medicina', NULL, 16.90, 'desparasitante_felino_5kg.jpg', 100, 10, 11.83, '2025-12-10 01:22:08'),
(21, 'Shampoo Medicado Antipulgas 250ml', 'medicina', NULL, 18.90, 'shampoo_medicado_250ml.jpg', 100, 10, 13.23, '2025-12-10 01:22:08'),
(22, 'Colirio Ocular para Mascotas', 'medicina', NULL, 14.50, 'colirio_mascotas.jpg', 100, 10, 10.15, '2025-12-10 01:22:08'),
(23, 'Pomada Antibiótica Veterinaria', 'medicina', NULL, 22.00, 'pomada_antibiotica.jpg', 100, 10, 15.40, '2025-12-10 01:22:08'),
(24, 'Comedero Plástico Pequeño', 'accesorio', NULL, 8.50, 'comedero_pequeno.jpg', 100, 10, 5.95, '2025-12-10 01:22:08'),
(25, 'Comedero Metálico Grande', 'accesorio', NULL, 15.90, 'comedero_metalico_grande.jpg', 100, 10, 11.13, '2025-12-10 01:22:08'),
(26, 'Bebedero Automático 2L', 'accesorio', NULL, 34.90, 'bebedero_automatico_2l.jpg', 100, 10, 24.43, '2025-12-10 01:22:08'),
(27, 'Correa Nylon Mediana', 'accesorio', NULL, 12.00, 'correa_nylon_mediana.jpg', 100, 10, 8.40, '2025-12-10 01:22:08'),
(28, 'Correa Retráctil 5m', 'accesorio', NULL, 25.00, 'correa_retractil_5m.jpg', 100, 10, 17.50, '2025-12-10 01:22:08'),
(29, 'Collar Ajustable para Perro', 'accesorio', NULL, 9.50, 'collar_ajustable_perro.jpg', 100, 10, 6.65, '2025-12-10 01:22:08'),
(30, 'Collar con Cascabel para Gato', 'accesorio', NULL, 7.50, 'collar_cascabel_gato.jpg', 100, 10, 5.25, '2025-12-10 01:22:08'),
(31, 'Arnés para Perro Mediano', 'accesorio', NULL, 29.00, 'arnes_perro_mediano.jpg', 100, 10, 20.30, '2025-12-10 01:22:08'),
(32, 'Pelota de Goma para Perros', 'juguete', NULL, 6.50, 'pelota_goma_perro.jpg', 100, 10, 4.55, '2025-12-10 01:22:08'),
(33, 'Hueso de Caucho', 'juguete', NULL, 9.90, 'hueso_caucho.jpg', 100, 10, 6.93, '2025-12-10 01:22:08'),
(34, 'Ratón de Juguete para Gatos', 'juguete', NULL, 5.50, 'raton_juguete_gato.jpg', 100, 10, 3.85, '2025-12-10 01:22:08'),
(35, 'Varita con Plumas para Gatos', 'juguete', NULL, 8.50, 'varita_plumas_gato.jpg', 100, 10, 5.95, '2025-12-10 01:22:08'),
(36, 'Cuerda Trenzada para Perros', 'juguete', NULL, 12.90, 'cuerda_trenzada.jpg', 100, 10, 9.03, '2025-12-10 01:22:08'),
(37, 'Arena Sanitaria 5KG', 'higiene', NULL, 18.90, 'arena_sanitaria_5kg.jpg', 100, 10, 13.23, '2025-12-10 01:22:08'),
(38, 'Arena Sanitaria 10KG', 'higiene', NULL, 29.90, 'arena_sanitaria_10kg.jpg', 100, 10, 20.93, '2025-12-10 01:22:08'),
(39, 'Pañales para Perros Talla S', 'higiene', NULL, 14.90, 'pañales_perro_s.jpg', 100, 10, 10.43, '2025-12-10 01:22:08'),
(40, 'Pañales para Perros Talla M', 'higiene', NULL, 16.90, 'pañales_perro_m.jpg', 100, 10, 11.83, '2025-12-10 01:22:08'),
(41, 'Toallitas Húmedas Mascotas 50u', 'higiene', NULL, 9.90, 'toallitas_mascotas_50.jpg', 100, 10, 6.93, '2025-12-10 01:22:08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_cart`
--

CREATE TABLE `user_cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) UNSIGNED NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `contrasena_hash` varchar(255) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `rol` enum('ADMIN','VENDEDOR') DEFAULT 'VENDEDOR',
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_completo`, `email`, `telefono`, `contrasena_hash`, `fecha_registro`, `rol`, `activo`) VALUES
(1, 'Samanta jauregui', 'samanta@gmail.com', NULL, '$2y$10$iUEOqk/VR.rQLCYBFU9SOuqmae6RPto8yB17fJbUJ.y4du8w7IFve', '2025-12-10 04:51:51', 'ADMIN', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `estado` enum('COMPLETADA','CANCELADA') DEFAULT 'COMPLETADA'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta_detalles`
--

CREATE TABLE `venta_detalles` (
  `id` int(11) NOT NULL,
  `venta_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `configuracion_backups`
--
ALTER TABLE `configuracion_backups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`);

--
-- Indices de la tabla `facturas_externas`
--
ALTER TABLE `facturas_externas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_numero_factura` (`numero_factura`),
  ADD KEY `fk_facturas_subido_por` (`subido_por`);

--
-- Indices de la tabla `movimientos_stock`
--
ALTER TABLE `movimientos_stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `user_cart`
--
ALTER TABLE `user_cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `venta_detalles`
--
ALTER TABLE `venta_detalles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venta_id` (`venta_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuracion_backups`
--
ALTER TABLE `configuracion_backups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `facturas_externas`
--
ALTER TABLE `facturas_externas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimientos_stock`
--
ALTER TABLE `movimientos_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de la tabla `user_cart`
--
ALTER TABLE `user_cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `venta_detalles`
--
ALTER TABLE `venta_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `configuracion_backups`
--
ALTER TABLE `configuracion_backups`
  ADD CONSTRAINT `configuracion_backups_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `facturas_externas`
--
ALTER TABLE `facturas_externas`
  ADD CONSTRAINT `fk_facturas_subido_por` FOREIGN KEY (`subido_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `movimientos_stock`
--
ALTER TABLE `movimientos_stock`
  ADD CONSTRAINT `movimientos_stock_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `venta_detalles`
--
ALTER TABLE `venta_detalles`
  ADD CONSTRAINT `venta_detalles_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `venta_detalles_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
