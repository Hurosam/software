<?php

namespace App\Servicios\CalculoSalarios;

use App\Contratos\CalculadorSalarioInterface;
use InvalidArgumentException;

/**
 * Estrategia para calcular el salario de contratistas.
 * Su lógica es: Horas Contratadas * Tarifa por Hora. El salario base se ignora.
 */
class CalculadorSalarioContratista implements CalculadorSalarioInterface
{
    public function calcularSalario(float $salarioBase, array $parametrosAdicionales = []): float
    {
        $horasContratadas = $parametrosAdicionales['horasContratadas'] ?? 0;
        $tarifaPorHora = $parametrosAdicionales['tarifaPorHora'] ?? 0;
        
        if ($horasContratadas <= 0) {
            throw new InvalidArgumentException("Las horas contratadas deben ser mayor a 0.");
        }
        
        if ($tarifaPorHora <= 0) {
            throw new InvalidArgumentException("La tarifa por hora debe ser mayor a 0.");
        }

        // El pago de un contratista es simplemente la multiplicación de sus horas por su tarifa.
        return $horasContratadas * $tarifaPorHora;
    }
}