<?php

// --- AUTOLOADER ---
spl_autoload_register(function ($className) {
    $path = __DIR__ . '/../src/' . str_replace(['App\\', '\\'], ['', '/'], $className) . '.php';
    if (file_exists($path)) { require_once $path; }
});

// --- IMPORTACIONES ---
use App\SistemaGestionEmpleados;
use App\Dominio\EmpleadoTiempoCompleto;
use App\Dominio\EmpleadoMedioTiempo;
use App\Dominio\EmpleadoContratista;
use App\Infraestructura\Persistencia\RepositorioEmpleadoMemoria;
use App\Servicios\Notificaciones\NotificadorCompuesto;
use App\Servicios\Notificaciones\ServicioNotificacionEmail;
use App\Servicios\Notificaciones\ServicioNotificacionSMS;
use App\Infraestructura\Factories\FactoryCalculadorSalario;
use App\Infraestructura\Factories\FactoryGeneradorReporte;

// --- INICIALIZACIÓN Y SESIONES ---
session_start();

if (!isset($_SESSION['sistema_iniciado'])) {
    $repositorio = new RepositorioEmpleadoMemoria();
    $notificador = new ServicioNotificacionEmail("smtp.example.com", "user", "pass");
    $sistema = new SistemaGestionEmpleados($repositorio, $notificador);
    $sistema->registrarEmpleado(new EmpleadoTiempoCompleto(0, "Juan", "Pérez", "juan.perez@example.com", 5000, 1200));
    $sistema->registrarEmpleado(new EmpleadoMedioTiempo(0, "María", "García", "maria.garcia@example.com", 3000, 25));
    $sistema->registrarEmpleado(new EmpleadoContratista(0, "Carlos", "López", "carlos.lopez@example.com", 0, 160, 35));
    $_SESSION['sistema'] = $sistema;
    $_SESSION['sistema_iniciado'] = true;
    $_SESSION['log_acciones'] = ["Sistema inicializado con 3 empleados."];
}

$sistema = $_SESSION['sistema'];
$logAcciones = &$_SESSION['log_acciones'];

FactoryCalculadorSalario::inicializarCalculadorasDefecto();
FactoryGeneradorReporte::inicializarGeneradoresDefecto();

// --- ROUTING Y LÓGICA DE ACCIONES ---
$accion = $_GET['accion'] ?? 'inicio';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($accion === 'procesar_nomina' && isset($_POST['canal_notificacion'])) {
        // (Lógica de procesamiento de nómina se mantiene igual)
        $canal = $_POST['canal_notificacion'];
        $notificador = null;
        switch ($canal) {
            case 'email': $notificador = new ServicioNotificacionEmail("smtp.example.com", "user", "pass"); break;
            case 'sms': $notificador = new ServicioNotificacionSMS("api-key", "api-url"); break;
            case 'ambos':
                $notificador = new NotificadorCompuesto();
                $notificador->agregarServicio(new ServicioNotificacionEmail("smtp.example.com", "user", "pass"));
                $notificador->agregarServicio(new ServicioNotificacionSMS("api-key", "api-url"));
                break;
        }
        if ($notificador) {
            $repositorio = new RepositorioEmpleadoMemoria();
            foreach($sistema->obtenerTodosLosEmpleados() as $emp) { $repositorio->guardarEmpleado($emp); }
            $sistemaConNuevoNotificador = new SistemaGestionEmpleados($repositorio, $notificador);
            $resultados = $sistemaConNuevoNotificador->procesarNominaCompleta();
            $logAcciones[] = "Nómina procesada por '{$canal}'. " . count($resultados['pagosExitosos']) . " pagos.";
        }
    }
    header("Location: index.php?accion=inicio");
    exit;
}

if ($accion === 'generar_reporte' && isset($_GET['formato'])) {
    // (Lógica de generación de reportes se mantiene igual)
    $formato = strtoupper($_GET['formato']);
    $contenido = $sistema->generarReporte($formato);
    $nombreArchivo = "reporte_empleados_" . date('Ymd');
    switch($formato) {
        case 'EXCEL': header('Content-Type: text/csv'); $nombreArchivo .= '.csv'; break;
        case 'JSON': header('Content-Type: application/json'); $nombreArchivo .= '.json'; break;
        case 'PDF': header('Content-Type: text/plain'); $nombreArchivo .= '.txt'; break;
    }
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
    echo $contenido;
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Gestión de Empleados</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 0; background-color: #f0f2f5; }
        .layout { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background-color: #2c3e50; color: #ecf0f1; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { margin: 0 0 20px 0; border-bottom: 1px solid #34495e; padding-bottom: 10px; }
        .sidebar nav a { display: block; color: #ecf0f1; text-decoration: none; padding: 10px 15px; margin-bottom: 5px; border-radius: 4px; transition: background-color 0.2s; }
        .sidebar nav a:hover, .sidebar nav a.active { background-color: #3498db; }
        .main-content { flex-grow: 1; padding: 30px; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 25px; margin-bottom: 25px; }
        h1, h3 { color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
        .log { background-color: #333; color: #fff; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 14px; max-height: 200px; overflow-y: auto; }
        input[type=submit], .btn { display: inline-block; text-decoration: none; background-color: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-right: 10px; }
        input[type=submit]:hover, .btn:hover { background-color: #2980b9; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group select, .form-group input[type=text] { width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; box-sizing: border-box; }
        .alert { padding: 15px; margin-top: 20px; border-radius: 5px; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <h2>Menú</h2>
            <nav>
                <a href="index.php?accion=inicio" class="<?= $accion === 'inicio' ? 'active' : '' ?>">Dashboard</a>
                <a href="index.php?accion=ver_empleados" class="<?= $accion === 'ver_empleados' ? 'active' : '' ?>">Ver Empleados</a>
                <a href="index.php?accion=resumen_nomina" class="<?= $accion === 'resumen_nomina' ? 'active' : '' ?>">Resumen de Nómina</a>
                <a href="index.php?accion=reportes" class="<?= $accion === 'reportes' ? 'active' : '' ?>">Generar Reportes</a>
            </nav>
        </aside>

        <main class="main-content">
            <?php if ($accion === 'inicio'): ?>
                <h1>Dashboard</h1>
                <div class="card">
                    <h3>Procesar Nómina</h3>
                    <p>Calcula los salarios y envía notificaciones por el canal que elijas.</p>
                    <form action="index.php?accion=procesar_nomina" method="POST">
                        <div class="form-group">
                            <label for="canal_notificacion">Canal de Notificación:</label>
                            <select name="canal_notificacion" id="canal_notificacion">
                                <option value="email">Solo Email</option>
                                <option value="sms">Solo SMS</option>
                                <option value="ambos">Ambos (Email y SMS)</option>
                            </select>
                        </div>
                        <input type="submit" value="Procesar Nómina Completa">
                    </form>
                </div>
                <div class="card">
                    <h3>Log de Acciones Recientes</h3>
                    <div class="log">
                        <?php foreach (array_reverse($logAcciones) as $log): ?>
                            <div><?= date('H:i:s') . ': ' . htmlspecialchars($log) ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>

            <?php elseif ($accion === 'ver_empleados'): ?>
                <h1>Lista de Empleados</h1>
                <div class="card">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre Completo</th>
                                <th>Email</th>
                                <th>Tipo</th>
                                <th>Salario Base</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sistema->obtenerTodosLosEmpleados() as $empleado): ?>
                            <tr>
                                <td><?= htmlspecialchars($empleado->getId()) ?></td>
                                <td><?= htmlspecialchars($empleado->getNombreCompleto()) ?></td>
                                <td><?= htmlspecialchars($empleado->getEmail()) ?></td>
                                <td><?= htmlspecialchars($empleado->getTipoEmpleado()) ?></td>
                                <td>$<?= number_format($empleado->getSalarioBase(), 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($accion === 'resumen_nomina'): ?>
                <h1>Resumen de Nómina</h1>
                
                <div class="card">
                    <h3>Buscar Salario por Empleado</h3>
                    <form action="index.php" method="GET">
                        <input type="hidden" name="accion" value="resumen_nomina">
                        <div class="form-group">
                            <label for="id_empleado">Introduce el ID del Empleado:</label>
                            <input type="text" id="id_empleado" name="id_empleado" placeholder="Ej: 1, 2 o 3" value="<?= htmlspecialchars($_GET['id_empleado'] ?? '') ?>">
                        </div>
                        <input type="submit" value="Buscar Empleado">
                    </form>
                </div>

                <?php
                $idBuscado = filter_input(INPUT_GET, 'id_empleado', FILTER_VALIDATE_INT);

                if ($idBuscado):
                    $empleado = null;
                    $errorBusqueda = '';
                    try {
                        $empleado = $sistema->obtenerEmpleado($idBuscado);
                        if ($empleado) {
                            $salarioCalculado = $sistema->calcularSalarioEmpleado($idBuscado);
                        } else {
                            $errorBusqueda = "No se encontró ningún empleado con el ID {$idBuscado}.";
                        }
                    } catch (Exception $e) {
                        $errorBusqueda = "Error al procesar la búsqueda: " . $e->getMessage();
                    }
                ?>
                    <?php if ($empleado): ?>
                        <div class="card">
                            <h3>Detalle del Salario para: <?= htmlspecialchars($empleado->getNombreCompleto()) ?> (ID: <?= $empleado->getId() ?>)</h3>
                            <ul>
                                <li><strong>Tipo de Empleado:</strong> <?= htmlspecialchars($empleado->getTipoEmpleado()) ?></li>
                                <li><strong>Salario Base Registrado:</strong> $<?= number_format($empleado->getSalarioBase(), 2) ?></li>
                                <li>
                                    <strong>Parámetros de Cálculo:</strong>
                                    <?php 
                                        $params = $empleado->getParametrosCalculoSalario();
                                        if (empty($params)) { echo "Ninguno"; } 
                                        else { foreach ($params as $key => $value) { echo htmlspecialchars(ucfirst(preg_replace('/(?<!^)[A-Z]/', ' $0', $key))) . ": " . htmlspecialchars($value) . " "; } }
                                    ?>
                                </li>
                                <li style="font-size: 1.2em; font-weight: bold; margin-top: 10px;">
                                    <strong>Salario Calculado a Pagar:</strong> $<?= number_format($salarioCalculado, 2) ?>
                                </li>
                            </ul>
                        </div>
                    <?php elseif ($errorBusqueda): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($errorBusqueda) ?></div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="card">
                        <?php $resumen = $sistema->obtenerResumenNomina(); ?>
                        <h3>Estadísticas Generales de la Nómina</h3>
                        <div class="alert alert-info">Introduce un ID en el formulario de arriba para ver el desglose de un empleado específico.</div>
                        <ul>
                            <li><strong>Total de Empleados:</strong> <?= $resumen['totalEmpleados'] ?></li>
                            <li><strong>Salario Total a Pagar:</strong> $<?= number_format($resumen['salarioTotal'], 2) ?></li>
                            <li><strong>Salario Promedio:</strong> $<?= number_format($resumen['salarioPromedio'], 2) ?></li>
                            <li><strong>Salario Mínimo Calculado:</strong> $<?= number_format($resumen['salarioMinimo'], 2) ?></li>
                            <li><strong>Salario Máximo Calculado:</strong> $<?= number_format($resumen['salarioMaximo'], 2) ?></li>
                        </ul>
                    </div>
                <?php endif; ?>

            <?php elseif ($accion === 'reportes'): ?>
                <h1>Generación de Reportes</h1>
                <div class="card">
                    <h3>Descargar Reporte de Empleados</h3>
                    <p>Selecciona un formato para descargar el listado completo de empleados.</p>
                    <a href="index.php?accion=generar_reporte&formato=excel" class="btn">Descargar EXCEL (CSV)</a>
                    <a href="index.php?accion=generar_reporte&formato=json" class="btn">Descargar JSON</a>
                    <a href="index.php?accion=generar_reporte&formato=pdf" class="btn">Descargar Reporte (TXT)</a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>