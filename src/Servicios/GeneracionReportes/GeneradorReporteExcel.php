<?php

namespace App\Servicios\GeneracionReportes;

use App\Contratos\GeneradorReporteInterface;
use App\Dominio\EmpleadoBase;
use InvalidArgumentException;

/**
 * Estrategia para generar un reporte en formato compatible con Excel (CSV).
 * Un archivo CSV (Comma-Separated Values) es la forma más simple y universal
 * de crear hojas de cálculo.
 */
class GeneradorReporteExcel implements GeneradorReporteInterface
{
    public function generarReporte(array $datosEmpleados): string
    {
        if (empty($datosEmpleados)) {
            throw new InvalidArgumentException("No hay datos de empleados para generar el reporte.");
        }

        // Encabezados de las columnas del CSV.
        $encabezados = [
            'ID',
            'Nombre Completo',
            'Email',
            'Tipo Empleado',
            'Salario Base',
            'Fecha Contratacion'
        ];

        // Usamos fputcsv para manejar correctamente las comas y comillas dentro de los datos.
        $stream = fopen('php://memory', 'w');
        fputcsv($stream, $encabezados);

        foreach ($datosEmpleados as $empleado) {
            if (!$empleado instanceof EmpleadoBase) {
                continue;
            }
            
            $fila = [
                $empleado->getId(),
                $empleado->getNombreCompleto(),
                $empleado->getEmail(),
                $empleado->getTipoEmpleado(),
                $empleado->getSalarioBase(),
                $empleado->getFechaContratacion()->format('Y-m-d')
            ];
            
            fputcsv($stream, $fila);
        }
        
        // Rebobinamos el puntero del stream para leer su contenido.
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);
        
        return $csv;
    }
}