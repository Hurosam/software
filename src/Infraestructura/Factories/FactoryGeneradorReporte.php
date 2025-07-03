<?php

namespace App\Infraestructura\Factories;

use App\Contratos\GeneradorReporteInterface;
use App\Servicios\GeneracionReportes\GeneradorReporteExcel;
use App\Servicios\GeneracionReportes\GeneradorReporteJSON;
use App\Servicios\GeneracionReportes\GeneradorReportePDF;
use RuntimeException;

/**
 * Factory para crear instancias de generadores de reportes.
 */
class FactoryGeneradorReporte
{
    /** @var GeneradorReporteInterface[] */
    private static array $generadores = [];

    /**
     * Registra una implementación de generador para un formato de reporte.
     */
    public static function registrarGenerador(string $formato, GeneradorReporteInterface $generador): void
    {
        self::$generadores[strtoupper($formato)] = $generador;
    }

    /**
     * Obtiene el generador apropiado para el formato solicitado.
     * @throws RuntimeException si no existe un generador para el formato.
     */
    public static function obtenerGenerador(string $formato): GeneradorReporteInterface
    {
        $formato = strtoupper($formato);
        
        if (!isset(self::$generadores[$formato])) {
            throw new RuntimeException("No existe un generador de reporte registrado para el formato: {$formato}");
        }

        return self::$generadores[$formato];
    }

    /**
     * Carga las implementaciones por defecto del sistema.
     */
    public static function inicializarGeneradoresDefecto(): void
    {
        if (empty(self::$generadores)) {
            self::registrarGenerador("JSON", new GeneradorReporteJSON());
            self::registrarGenerador("PDF", new GeneradorReportePDF());
            self::registrarGenerador("EXCEL", new GeneradorReporteExcel());
        }
    }
}