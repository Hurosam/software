<?php

// 1. AUTOLOADER: Carga las clases de /src automáticamente cuando se necesitan.
spl_autoload_register(function ($nombreClase) {
    // La ruta base es el directorio padre de __DIR__ (que es /public), o sea, la raíz del proyecto.
    $rutaBase = dirname(__DIR__) . '/src/';
    $rutaArchivo = str_replace(['App\\', '\\'], ['', '/'], $nombreClase) . '.php';
    $rutaCompleta = $rutaBase . $rutaArchivo;

    if (file_exists($rutaCompleta)) {
        require_once $rutaCompleta;
    }
});

use App\Servicios\ServicioDeNomina;
// ¡Importamos la clase de repositorio de BD simulada que crearemos!
use App\Servicios\Repositorios\RepositorioEmpleadosSimulado;
use App\Servicios\Notificadores\ServicioNotificacionEmail;

// 2. ROUTING: Determinar qué página se va a mostrar
$paginasPermitidas = ['inicio', 'lista_empleados', 'procesar_nomina'];
$paginaActual = $_GET['pagina'] ?? 'inicio'; // Si no hay parámetro, es 'inicio'

// Medida de seguridad: si la página no está en la lista, forzamos 'inicio'
if (!in_array($paginaActual, $paginasPermitidas)) {
    $paginaActual = 'inicio';
}


// 3. PREPARACIÓN DE DATOS (Lógica del Controlador)
// Aquí preparamos las variables que necesitarán nuestras vistas (templates).

// Instanciamos nuestro repositorio simulado.
$repositorio = new RepositorioEmpleadosSimulado();
$notificador = new ServicioNotificacionEmail();
$servicioNomina = new ServicioDeNomina($repositorio, $notificador);

$listaEmpleados = []; // Inicializamos la variable

// Si la página es 'lista_empleados', preparamos los datos para la tabla.
if ($paginaActual === 'lista_empleados') {
    // Obtenemos todos los empleados del repositorio simulado
    $empleadosDesdeRepo = $repositorio->buscarTodos();
    
    // Transformamos los objetos en un array simple para la vista
    foreach ($empleadosDesdeRepo as $empleado) {
        $detalles = '';
        if ($empleado instanceof App\Empleados\EmpleadoTiempoCompleto) {
            $salario = $empleado->calcularSalario();
            $detalles = "Salario Mensual: $" . number_format($salario, 2);
        } elseif ($empleado instanceof App\Empleados\Contratista) {
             $salario = $empleado->calcularSalario();
             $detalles = "Salario Total: $" . number_format($salario, 2);
        }
        
        $listaEmpleados[] = [
            'id' => $empleado->obtenerId(),
            'nombre' => $empleado->obtenerNombre(),
            'tipo' => $empleado instanceof App\Empleados\EmpleadoTiempoCompleto ? 'Tiempo Completo' : 'Contratista',
            'detalles' => $detalles
        ];
    }
}


// 4. RENDERIZADO DE LA VISTA: Incluimos los fragmentos de HTML
// Esta es la parte de la "Vista" del patrón MVC.
require_once dirname(__DIR__) . '/templates/_header.php';
require_once dirname(__DIR__) . '/templates/' . $paginaActual . '.php';
require_once dirname(__DIR__) . '/templates/_footer.php';