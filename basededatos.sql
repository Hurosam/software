-- --------------------------------------------------------
-- SCRIPT DE CONFIGURACIÓN PARA LA BASE DE DATOS 'nomina_db'
-- --------------------------------------------------------

--
-- PASO 1: Crear la base de datos si no existe.
-- Se utiliza `utf8mb4` para un soporte completo de caracteres, incluyendo emojis.
--
CREATE DATABASE IF NOT EXISTS `nomina_db` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;


--
-- PASO 2: Seleccionar la base de datos para asegurar que los siguientes comandos se apliquen a ella.
--
USE `nomina_db`;


--
-- PASO 3: Crear la tabla `empleados` si no existe.
--
CREATE TABLE IF NOT EXISTS `empleados` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `apellido` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `tipo` ENUM('Tiempo Completo', 'Medio Tiempo', 'Contratista') NOT NULL,
  `salario_base` DECIMAL(12, 2) NOT NULL,
  `fecha_contratacion` DATETIME NOT NULL,
  
  -- Parámetros adicionales (pueden ser NULL dependiendo del tipo)
  `bono_anual` DECIMAL(12, 2) NULL,
  `horas_semanales` INT UNSIGNED NULL,
  `horas_contratadas` INT UNSIGNED NULL,
  `tarifa_por_hora` DECIMAL(8, 2) NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `email_unico` (`email`) COMMENT 'Asegura que no haya emails duplicados.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
-- PASO 4: Vaciar la tabla antes de insertar nuevos datos para evitar duplicados en cada ejecución.
-- (Opcional, pero recomendado para un entorno de prueba limpio).
--
TRUNCATE TABLE `empleados`;


--
-- PASO 5: Insertar los datos de ejemplo.
--
INSERT INTO `empleados` 
  (`id`, `nombre`, `apellido`, `email`, `tipo`, `salario_base`, `fecha_contratacion`, `bono_anual`, `horas_semanales`, `horas_contratadas`, `tarifa_por_hora`) 
VALUES
  (1, 'Juan', 'Pérez', 'juan.perez@example.com', 'Tiempo Completo', 5000.00, NOW(), 1200.00, NULL, NULL, NULL),
  (2, 'María', 'García', 'maria.garcia@example.com', 'Medio Tiempo', 3000.00, NOW(), NULL, 25, NULL, NULL),
  (3, 'Carlos', 'López', 'carlos.lopez@example.com', 'Contratista', 0.00, NOW(), NULL, NULL, 160, 35.00);