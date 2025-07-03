<?php

namespace App\Servicios\GeneracionReportes;

use App\Contratos\GeneradorReporteInterface;
use App\Dominio\EmpleadoBase;
use InvalidArgumentException;

/**
 * Estrategia para generar un reporte en formato PDF (simulado).
 * En una aplicación real, esta clase usaría una librería como FPDF o DomPDF.
 */
class GeneradorReportePDF implements GeneradorReporteInterface
{
    public function generarReporte(array $datosEmpleados): string
    {
        if (empty($datosEmpleados)) {
            throw new InvalidArgumentException("No hay datos de empleados para generar el reporte.");
        }

        // Simulación de la creación del contenido de un archivo PDF.
        $contenido = "=== REPORTE DE EMPLEADOS (PDF) ===\n\n";
        $contenido .= "Fecha de Generación: " . date('Y-m-d H:i:s') . "\n";
        $contenido .= "Total de Empleados: " . count($datosEmpleados) . "\n";
        $contenido .= str_repeat("-", 40) . "\n\n";

        foreach ($datosEmpleados as $empleado) {
            if (!$empleado instanceof EmpleadoBase) {
                continue;
            }

            $contenido .= "ID: " . $empleado->getId() . "\n";
            $contenido .= "Nombre: " . $empleado->getNombreCompleto() . "\n";
            $contenido .= "Email: " . $empleado->getEmail() . "\n";
            $contenido .= "Tipo: " . $empleado->getTipoEmpleado() . "\n";
            $contenido .= "Salario Base: $" . number_format($empleado->getSalarioBase(), 2) . "\n";
            $contenido .= "Fecha de Contratación: " . $empleado->getFechaContratacion()->format('Y-m-d') . "\n";
            $contenido .= "------------------------\n";
        }

        return $contenido;
    }
}