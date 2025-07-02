<?php

// --- AUTOLOADER ---
// Este código es crucial. Carga las clases automáticamente cuando se usan por primera vez.
// Esto evita tener que hacer un 'require_once' para cada uno de los 15 archivos que creamos.
spl_autoload_register(function ($nombreClaseCompleto) {
    // Convierte el namespace (ej: App\Contratos\EmpleadoInterface)
    // a una ruta de archivo relativa (ej: contratos/EmpleadoInterface.php)
    $rutaArchivo = str_replace(['App\\', '\\'], ['', '/'], $nombreClaseCompleto) . '.php';

    // Verifica si el archivo existe antes de incluirlo.
    if (file_exists($rutaArchivo)) {
        require_once $rutaArchivo;
    }
});

// --- IMPORTACIONES (use) ---
// Hacemos esto para poder usar los nombres cortos de las clases (ej: Conexion)
// en lugar de los nombres completos (ej: App\Conexion\Conexion).
use App\Conexion\Conexion;
use App\Conexion\RepositorioEmpleados;
use App\Servicios\ServicioNomina;
use App\Clases\Notificadores\NotificadorEmail;
use App\Clases\Notificadores\NotificadorSms;
use App\Clases\Reportes\ReporteJson;
use App\Clases\Reportes\ReportePdf;

// --- CONFIGURACIÓN Y ARMADO DE LA APLICACIÓN (INYECCIÓN DE DEPENDENCIAS) ---

// 1. Conexión a la Base de Datos
$pdo = (new Conexion())->conectar();

// Si la conexión falla, detenemos todo y mostramos un error claro.
if (!$pdo) {
    die("<h1>Error Crítico</h1><p>No se pudo conectar a la base de datos. Revisa los datos en <code>/conexion/Conexion.php</code> y asegúrate de que MySQL esté corriendo en Laragon y la base de datos 'nomina_db' exista.</p>");
}

// 2. Creación de las dependencias (las "piezas" de nuestro sistema)
$repositorio = new RepositorioEmpleados($pdo);
$notificadorEmail = new NotificadorEmail();
$notificadorSms = new NotificadorSms();
$reporteJson = new ReporteJson();
$reportePdf = new ReportePdf();

// 3. Creación de los servicios principales, inyectando las dependencias.
//    Aquí se demuestra la flexibilidad: podemos crear un servicio que notifica
//    por email y otro que notifica por SMS, y ambos funcionan.
$servicioNominaConEmail = new ServicioNomina($repositorio, $notificadorEmail);
$servicioNominaConSms = new ServicioNomina($repositorio, $notificadorSms);


// --- EJECUCIÓN DE LA LÓGICA Y RECOPILACIÓN DE RESULTADOS ---

// A. Procesamos los pagos de varios empleados.
$resultadosPago = [];
$resultadosPago[] = $servicioNominaConEmail->procesarPago(101); // Ana (tiempo completo) por Email
$resultadosPago[] = $servicioNominaConSms->procesarPago(202);    // Luis (contratista) por SMS
$resultadosPago[] = $servicioNominaConEmail->procesarPago(303); // Elena (medio tiempo) por Email
$resultadosPago[] = $servicioNominaConEmail->procesarPago(999); // Empleado que no existe para probar el error

// B. Generamos los reportes.
$todosLosEmpleados = $repositorio->obtenerTodos();
$reporteJsonGenerado = $reporteJson->generar($todosLosEmpleados);
$reportePdfGenerado = $reportePdf->generar($todosLosEmpleados);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Demostración de Principios SOLID en PHP</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; line-height: 1.6; padding: 20px; background-color: #f8f9fa; color: #212529; }
        .container { max-width: 900px; margin: 2rem auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1, h2 { color: #0d6efd; border-bottom: 2px solid #dee2e6; padding-bottom: 0.5rem; margin-bottom: 1.5rem; }
        pre { background: #2d2d2d; color: #ccc; padding: 15px; border-radius: 5px; white-space: pre-wrap; font-family: "SF Mono", "Fira Code", monospace; font-size: 0.9em; }
    </style>
</head>
<body>
<div class="container">
    <h1>Resultados del Sistema de Nómina</h1>

    <h2>Procesamiento de Pagos</h2>
    <?php foreach ($resultadosPago as $resultado): ?>
        <pre><?php echo htmlspecialchars($resultado); ?></pre>
    <?php endforeach; ?>

    <h2>Generación de Reportes</h2>

    <h3>Reporte en formato JSON</h3>
    <pre><?php echo htmlspecialchars($reporteJsonGenerado); ?></pre>

    <h3>Reporte en formato PDF (Simulado)</h3>
    <pre><?php echo htmlspecialchars($reportePdfGenerado); ?></pre>

</div>
</body>
</html>