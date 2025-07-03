<?php

// --- AUTOLOADER ---
// Carga las clases automáticamente cuando se usan, basándose en su namespace.
spl_autoload_register(function ($className) {
    // Convierte App\Contratos\CalculadorSalarioInterface -> src/Contratos/CalculadorSalarioInterface.php
    $path = __DIR__ . '/src/' . str_replace(['App\\', '\\'], ['', '/'], $className) . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});

// --- IMPORTACIÓN DE CLASES (Opcional, pero mejora la legibilidad) ---
use App\SistemaGestionEmpleados;
use App\Dominio\EmpleadoTiempoCompleto;
use App\Dominio\EmpleadoMedioTiempo;
use App\Dominio\EmpleadoContratista;
use App\Infraestructura\Persistencia\RepositorioEmpleadoMemoria;
use App\Servicios\Notificaciones\NotificadorCompuesto;
use App\Servicios\Notificaciones\ServicioNotificacionEmail;
use App\Servicios\Notificaciones\ServicioNotificacionSMS;
use App\Servicios\CalculoSalarios\CalculadorSalarioContratista;
use App\Servicios\GeneracionReportes\GeneradorReporteJSON;

/**
 * Clase de demostración para ejecutar el sistema.
 */
class EjemploUsoSistema
{
    public static function ejecutarDemo(): void
    {
        echo "SISTEMA DE GESTIÓN DE EMPLEADOS - DEMO\n";
        echo "=====================================\n\n";

        try {
            // 1. Configurar el sistema
            echo "=== 1. CONFIGURANDO EL SISTEMA ===\n";
            $repositorio = new RepositorioEmpleadoMemoria();
            $notificador = new NotificadorCompuesto();
            $notificador->agregarServicio(new ServicioNotificacionEmail("smtp.example.com", "user", "pass"));
            $notificador->agregarServicio(new ServicioNotificacionSMS("api-key", "api-url"));
            $sistema = new SistemaGestionEmpleados($repositorio, $notificador);
            echo "Sistema configurado con Repositorio en Memoria y Notificador Compuesto.\n\n";

            // 2. Registrar empleados
            echo "=== 2. REGISTRANDO EMPLEADOS ===\n";
            $sistema->registrarEmpleado(new EmpleadoTiempoCompleto(0, "Juan", "Pérez", "juan.perez@example.com", 5000, 1200));
            $sistema->registrarEmpleado(new EmpleadoMedioTiempo(0, "María", "García", "maria.garcia@example.com", 3000, 25));
            $sistema->registrarEmpleado(new EmpleadoContratista(0, "Carlos", "López", "carlos.lopez@example.com", 0, 160, 35));
            echo "3 empleados registrados.\n\n";

            // 3. Calcular salarios
            echo "=== 3. CALCULANDO SALARIOS ===\n";
            echo "Salario Juan (ID 1): $" . number_format($sistema->calcularSalarioEmpleado(1), 2) . "\n";
            echo "Salario María (ID 2): $" . number_format($sistema->calcularSalarioEmpleado(2), 2) . "\n";
            echo "Salario Carlos (ID 3): $" . number_format($sistema->calcularSalarioEmpleado(3), 2) . "\n\n";

            // 4. Generar reportes
            echo "=== 4. GENERANDO REPORTES ===\n";
            echo "Reporte Excel generado:\n" . $sistema->generarReporte("EXCEL") . "\n";

            // 5. Procesar pagos
            echo "=== 5. PROCESANDO NÓMINA COMPLETA ===\n";
            $resultadoNomina = $sistema->procesarNominaCompleta();
            echo "Pagos procesados exitosamente: " . count($resultadoNomina['pagosExitosos']) . "\n\n";

            // 6. Obtener resumen de nómina
            echo "=== 6. OBTENIENDO RESUMEN DE NÓMINA ===\n";
            $resumen = $sistema->obtenerResumenNomina();
            echo "Salario total a pagar: $" . number_format($resumen['salarioTotal'], 2) . "\n";
            echo "Salario promedio: $" . number_format($resumen['salarioPromedio'], 2) . "\n\n";

            // 7. Demostrar extensibilidad (OCP)
            echo "=== 7. DEMOSTRANDO EXTENSIBILIDAD (OCP) ===\n";
            $sistema->registrarTipoEmpleado("Freelancer", new CalculadorSalarioContratista());
            echo "Nuevo tipo de empleado 'Freelancer' registrado.\n";
            $sistema->registrarFormatoReporte("XML", new GeneradorReporteJSON());
            echo "Nuevo formato de reporte 'XML' registrado.\n\n";

            echo "=== DEMO COMPLETADA EXITOSAMENTE ===\n";

        } catch (Exception $e) {
            echo "\n--- ERROR EN LA DEMO ---\n";
            echo "Mensaje: " . $e->getMessage() . "\n";
            echo "Archivo: " . $e->getFile() . "\n";
            echo "Línea: " . $e->getLine() . "\n";
        }
    }
}

// Ejecutar la demostración
EjemploUsoSistema::ejecutarDemo();