<?php

namespace App\Servicios\CalculoSalarios;

use App\Contratos\CalculadorSalarioInterface;
use InvalidArgumentException;

/**
 * Estrategia para calcular el salario de empleados de tiempo completo.
 * Su lógica es: Salario Base Mensual + (Bono Anual / 12).
 */
class CalculadorSalarioTiempoCompleto implements CalculadorSalarioInterface
{
    public function calcularSalario(float $salarioBase, array $parametrosAdicionales = []): float
    {
        if ($salarioBase < 0) {
            throw new InvalidArgumentException("El salario base no puede ser negativo.");
        }

        $bonoAnual = $parametrosAdicionales['bonoAnual'] ?? 0;
        
        if ($bonoAnual < 0) {
            throw new InvalidArgumentException("El bono anual no puede ser negativo.");
        }

        // Salario mensual + la parte proporcional del bono anual para ese mes.
        return $salarioBase + ($bonoAnual / 12);
    }
}