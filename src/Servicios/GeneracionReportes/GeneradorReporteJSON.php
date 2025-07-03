<?php

namespace App\Servicios\GeneracionReportes;

use App\Contratos\GeneradorReporteInterface;
use App\Dominio\EmpleadoBase;
use InvalidArgumentException;
use RuntimeException;

/**
 * Estrategia para generar un reporte de empleados en formato JSON.
 */
class GeneradorReporteJSON implements GeneradorReporteInterface
{
    public function generarReporte(array $datosEmpleados): string
    {
        if (empty($datosEmpleados)) {
            throw new InvalidArgumentException("No hay datos de empleados para generar el reporte.");
        }

        $reporte = [
            'titulo' => 'Reporte de Empleados',
            'fechaGeneracion' => date('Y-m-d H:i:s'),
            'totalEmpleados' => count($datosEmpleados),
            'empleados' => []
        ];

        foreach ($datosEmpleados as $empleado) {
            // Aseguramos que solo procesamos objetos de nuestro dominio.
            if (!$empleado instanceof EmpleadoBase) {
                continue;
            }

            $reporte['empleados'][] = [
                'id' => $empleado->getId(),
                'nombreCompleto' => $empleado->getNombreCompleto(),
                'email' => $empleado->getEmail(),
                'tipoEmpleado' => $empleado->getTipoEmpleado(),
                'salarioBase' => $empleado->getSalarioBase(),
                'fechaContratacion' => $empleado->getFechaContratacion()->format('Y-m-d')
            ];
        }

        // JSON_PRETTY_PRINT hace que la salida sea legible para humanos.
        // JSON_UNESCAPED_UNICODE asegura que los caracteres como 'ñ' o tildes se vean bien.
        $json = json_encode($reporte, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if ($json === false) {
            // Capturamos un posible error en la codificación JSON.
            throw new RuntimeException("Error al generar el reporte JSON: " . json_last_error_msg());
        }

        return $json;
    }
}