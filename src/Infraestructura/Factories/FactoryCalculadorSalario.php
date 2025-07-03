<?php

namespace App\Infraestructura\Factories;

use App\Contratos\CalculadorSalarioInterface;
use App\Servicios\CalculoSalarios\CalculadorSalarioContratista;
use App\Servicios\CalculoSalarios\CalculadorSalarioMedioTiempo;
use App\Servicios\CalculoSalarios\CalculadorSalarioTiempoCompleto;
use RuntimeException;

/**
 * Factory para crear instancias de calculadoras de salario.
 * (SRP: Su única responsabilidad es crear y gestionar calculadoras).
 * (OCP: Se pueden registrar nuevas calculadoras sin modificar esta clase).
 */
class FactoryCalculadorSalario
{
    /** @var CalculadorSalarioInterface[] */
    private static array $calculadoras = [];

    /**
     * Registra una implementación de calculadora para un tipo de empleado.
     */
    public static function registrarCalculadora(string $tipoEmpleado, CalculadorSalarioInterface $calculadora): void
    {
        self::$calculadoras[$tipoEmpleado] = $calculadora;
    }

    /**
     * Obtiene la calculadora apropiada para el tipo de empleado.
     * @throws RuntimeException si no existe una calculadora para el tipo solicitado.
     */
    public static function obtenerCalculadora(string $tipoEmpleado): CalculadorSalarioInterface
    {
        if (!isset(self::$calculadoras[$tipoEmpleado])) {
            throw new RuntimeException("No existe una calculadora de salario registrada para el tipo de empleado: {$tipoEmpleado}");
        }

        return self::$calculadoras[$tipoEmpleado];
    }

    /**
     * Carga las implementaciones por defecto del sistema.
     * (DRY: Centraliza la configuración inicial).
     */
    public static function inicializarCalculadorasDefecto(): void
    {
        if (empty(self::$calculadoras)) {
            self::registrarCalculadora("Tiempo Completo", new CalculadorSalarioTiempoCompleto());
            self::registrarCalculadora("Medio Tiempo", new CalculadorSalarioMedioTiempo());
            self::registrarCalculadora("Contratista", new CalculadorSalarioContratista());
        }
    }
}