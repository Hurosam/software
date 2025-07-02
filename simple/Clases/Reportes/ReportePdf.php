<?php
namespace App\Clases\Reportes;

use App\Contratos\ReporteInterface;

/**
 * Simula la generación de un reporte en formato PDF.
 * En un caso real, aquí usaríamos una librería como FPDF o DomPDF.
 */
class ReportePdf implements ReporteInterface
{
    public function generar(array $empleados): string {
        $contenido = "--- REPORTE PDF (SIMULADO) ---\n";
        $contenido .= "Total de empleados: " . count($empleados) . "\n";
        $contenido .= "------------------------------\n";
        foreach ($empleados as $empleado) {
            $contenido .= "ID: {$empleado->obtenerId()}, Nombre: {$empleado->obtenerNombre()}\n";
        }
        return $contenido;
    }
}