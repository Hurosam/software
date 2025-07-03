<?php

// --- AUTOLOADER MANUAL ---
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

// --- INICIALIZACIÓN Y SESIONES (Tu código funcional) ---
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
$mensajeFeedback = $_SESSION['mensaje_feedback'] ?? null;
unset($_SESSION['mensaje_feedback']);
$mensajeFeedbackError = $_SESSION['mensaje_feedback_error'] ?? null;
unset($_SESSION['mensaje_feedback_error']);

// Acciones POST (envío de formularios)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($accion === 'procesar_nomina' && isset($_POST['canal_notificacion'])) {
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
            header("Location: index.php?accion=inicio");
            exit;
        }

        // --- ¡NUEVA LÓGICA PARA AGREGAR EMPLEADO! ---
        if ($accion === 'agregar_empleado') {
            $tipo = $_POST['tipo'] ?? '';
            $nombre = $_POST['nombre'] ?? '';
            $apellido = $_POST['apellido'] ?? '';
            $email = $_POST['email'] ?? '';
            $salarioBase = (float)($_POST['salario_base'] ?? 0);
            
            $empleado = null;
            switch ($tipo) {
                case 'Tiempo Completo':
                    $bono = (float)($_POST['bono_anual'] ?? 0);
                    $empleado = new EmpleadoTiempoCompleto(0, $nombre, $apellido, $email, $salarioBase, $bono);
                    break;
                case 'Medio Tiempo':
                    $horas = (int)($_POST['horas_semanales'] ?? 20);
                    $empleado = new EmpleadoMedioTiempo(0, $nombre, $apellido, $email, $salarioBase, $horas);
                    break;
                case 'Contratista':
                    $horas = (int)($_POST['horas_contratadas'] ?? 0);
                    $tarifa = (float)($_POST['tarifa_por_hora'] ?? 0);
                    $empleado = new EmpleadoContratista(0, $nombre, $apellido, $email, 0, $horas, $tarifa);
                    break;
            }

            if ($empleado) {
                $sistema->registrarEmpleado($empleado);
                $_SESSION['log_acciones'][] = "Nuevo empleado '{$empleado->getNombreCompleto()}' registrado.";
                $_SESSION['mensaje_feedback'] = "¡Empleado registrado exitosamente!";
            } else {
                throw new Exception("Tipo de empleado no válido.");
            }

            header("Location: index.php?accion=ver_empleados");
            exit;
        }

    } catch (Exception $e) {
        $_SESSION['mensaje_feedback_error'] = "Error: " . $e->getMessage();
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

// Acciones GET (descarga de reportes)
if ($accion === 'generar_reporte' && isset($_GET['formato'])) {
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
        .form-group select, .form-group input[type=text], .form-group input[type=email], .form-group input[type=number] { width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; box-sizing: border-box; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .hidden { display: none; }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <h2>Menú</h2>
            <nav>
                <a href="index.php?accion=inicio" class="<?= $accion === 'inicio' ? 'active' : '' ?>">Dashboard</a>
                <a href="index.php?accion=ver_empleados" class="<?= $accion === 'ver_empleados' ? 'active' : '' ?>">Ver Empleados</a>
                <a href="index.php?accion=agregar_empleado" class="<?= $accion === 'agregar_empleado' ? 'active' : '' ?>">Agregar Empleado</a>
                <a href="index.php?accion=resumen_nomina" class="<?= $accion === 'resumen_nomina' ? 'active' : '' ?>">Resumen de Nómina</a>
                <a href="index.php?accion=reportes" class="<?= $accion === 'reportes' ? 'active' : '' ?>">Generar Reportes</a>
            </nav>
        </aside>
                <main class="main-content">
            
            <?php if ($mensajeFeedback): ?>
                <div class="alert alert-success"><?= htmlspecialchars($mensajeFeedback) ?></div>
            <?php endif; ?>
            <?php if ($mensajeFeedbackError): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($mensajeFeedbackError) ?></div>
            <?php endif; ?>

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

            <?php elseif ($accion === 'agregar_empleado'): ?>
                <h1>Agregar Nuevo Empleado</h1>
                <div class="card">
                    <form action="index.php?accion=agregar_empleado" method="POST">
                        <div class="form-group">
                            <label for="tipo">Tipo de Empleado</label>
                            <select name="tipo" id="tipo" required onchange="mostrarCamposEspecificos()">
                                <option value="Tiempo Completo">Tiempo Completo</option>
                                <option value="Medio Tiempo">Medio Tiempo</option>
                                <option value="Contratista">Contratista</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="nombre">Nombre</label>
                            <input type="text" id="nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="apellido">Apellido</label>
                            <input type="text" id="apellido" name="apellido" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <!-- Campos que se muestran/ocultan -->
                        <div id="campo-salario-base" class="form-group">
                            <label for="salario_base">Salario Base Mensual</label>
                            <input type="number" id="salario_base" name="salario_base" step="0.01" value="0">
                        </div>
                        <div id="campo-bono-anual" class="form-group">
                            <label for="bono_anual">Bono Anual</label>
                            <input type="number" id="bono_anual" name="bono_anual" step="0.01" value="0">
                        </div>
                        <div id="campo-horas-semanales" class="form-group hidden">
                            <label for="horas_semanales">Horas Semanales</label>
                            <input type="number" id="horas_semanales" name="horas_semanales" value="20">
                        </div>
                        <div id="campo-contratista" class="form-group hidden">
                            <label for="horas_contratadas">Horas Contratadas (por mes)</label>
                            <input type="number" id="horas_contratadas" name="horas_contratadas" value="0">
                            <label for="tarifa_por_hora" style="margin-top:10px;">Tarifa por Hora</label>
                            <input type="number" id="tarifa_por_hora" name="tarifa_por_hora" step="0.01" value="0">
                        </div>
                        
                        <input type="submit" value="Registrar Empleado">
                    </form>
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
                            <h3>Detalle del Salario para: <?= htmlspecialchars($empleado->getNombreCompleto()) ?> (ID: <?= htmlspecialchars($empleado->getId()) ?>)</h3>
                            <ul style="list-style: none; padding-left: 0; font-size: 1.1em;">
                                <li style="margin-bottom: 8px;">
                                    <strong>Tipo de Empleado:</strong> <?= htmlspecialchars($empleado->getTipoEmpleado()) ?>
                                </li>
                                <li style="margin-bottom: 8px;">
                                    <strong>Salario Base Registrado:</strong> $<?= number_format($empleado->getSalarioBase(), 2) ?>
                                </li>
                                <li style="margin-bottom: 8px;">
                                    <strong>Parámetros de Cálculo:</strong>
                                    <?php 
                                        $params = $empleado->getParametrosCalculoSalario();
                                        if (empty($params)) {
                                            echo "Ninguno";
                                        } else {
                                            $detalles = [];
                                            foreach ($params as $key => $value) {
                                                $label = ucfirst(preg_replace('/(?<!^)[A-Z]/', ' $0', $key));
                                                $valorFormateado = is_numeric($value) ? '$' . number_format($value, 2) : htmlspecialchars($value);
                                                if (str_contains(strtolower($key), 'horas')) {
                                                    $valorFormateado = htmlspecialchars($value);
                                                }
                                                $detalles[] = htmlspecialchars($label) . ": " . $valorFormateado;
                                            }
                                            echo implode('   |   ', $detalles);
                                        }
                                    ?>
                                </li>
                                <li style="font-size: 1.3em; font-weight: bold; margin-top: 15px; border-top: 1px solid #eee; padding-top: 15px; color: #27ae60;">
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
    <script>
        function mostrarCamposEspecificos() {
            const tipo = document.getElementById('tipo').value;
            
            const campoSalarioBase = document.getElementById('campo-salario-base');
            const campoBonoAnual = document.getElementById('campo-bono-anual');
            const campoHorasSemanales = document.getElementById('campo-horas-semanales');
            const campoContratista = document.getElementById('campo-contratista');

            // Ocultar todos los campos específicos primero
            campoSalarioBase.classList.add('hidden');
            campoBonoAnual.classList.add('hidden');
            campoHorasSemanales.classList.add('hidden');
            campoContratista.classList.add('hidden');

            // Mostrar los campos según el tipo seleccionado
            if (tipo === 'Tiempo Completo') {
                campoSalarioBase.classList.remove('hidden');
                campoBonoAnual.classList.remove('hidden');
            } else if (tipo === 'Medio Tiempo') {
                campoSalarioBase.classList.remove('hidden');
                campoHorasSemanales.classList.remove('hidden');
            } else if (tipo === 'Contratista') {
                campoContratista.classList.remove('hidden');
            }
        }
        // Llamar a la función al cargar la página para establecer el estado inicial correcto
        document.addEventListener('DOMContentLoaded', mostrarCamposEspecificos);
    </script>
</body>
</html>