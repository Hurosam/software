<?php

namespace App\Servicios;

use App\Infraestructura\Factories\FactoryGeneradorReporte;
use InvalidArgumentException;
use Exception;
use RuntimeException;

/**
 * Servicio encargado de la lógica de negocio para la generación de reportes.
 * (SRP: Su única responsabilidad es generar reportes).
 */
class ServicioGeneracionReportes
{
    public function generarReporte(array $empleados, string $formato): string
    {
        if (empty($empleados)) {
            throw new InvalidArgumentException("No hay empleados para generar el reporte.");
        }

        try {
            $generador = FactoryGeneradorReporte::obtenerGenerador($formato);
            return $generador->generarReporte($empleados);
        } catch (Exception $e) {
            throw new RuntimeException("Error al generar reporte en formato {$formato}: " . $e->getMessage(), 0, $e);
        }
    }
}