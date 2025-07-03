<?php

namespace App\Contratos;

use InvalidArgumentException;

/**
 * Interface para cualquier clase que calcule un salario.
 * Define el contrato para las diferentes estrategias de cálculo.
 */
interface CalculadorSalarioInterface
{
    /**
     * Calcula el salario de un empleado.
     *
     * @param float $salarioBase El salario base sobre el cual calcular.
     * @param array $parametrosAdicionales Parámetros específicos del tipo de empleado (ej: bono, horas).
     * @return float El salario final calculado.
     * @throws InvalidArgumentException Si los parámetros son inválidos.
     */
    public function calcularSalario(float $salarioBase, array $parametrosAdicionales = []): float;
}