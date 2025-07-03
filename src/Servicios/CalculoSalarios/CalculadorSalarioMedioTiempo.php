<?php

namespace App\Servicios\CalculoSalarios;

use App\Contratos\CalculadorSalarioInterface;
use InvalidArgumentException;

/**
 * Estrategia para calcular el salario de empleados de medio tiempo.
 * Su lógica es: Salario Base proporcional a las horas trabajadas,
 * asumiendo que el salario base es para 40 horas.
 */
class CalculadorSalarioMedioTiempo implements CalculadorSalarioInterface
{
    private const HORAS_TIEMPO_COMPLETO = 40;

    public function calcularSalario(float $salarioBase, array $parametrosAdicionales = []): float
    {
        if ($salarioBase < 0) {
            throw new InvalidArgumentException("El salario base no puede ser negativo.");
        }

        $horasSemanales = $parametrosAdicionales['horasSemanales'] ?? 20;
        
        if ($horasSemanales <= 0) {
            throw new InvalidArgumentException("Las horas semanales deben ser mayor a 0.");
        }

        // Calcula la proporción del salario basado en las horas trabajadas
        // respecto a una jornada completa estándar.
        return $salarioBase * ($horasSemanales / self::HORAS_TIEMPO_COMPLETO);
    }
}