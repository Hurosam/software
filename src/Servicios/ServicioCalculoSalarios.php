<?php

namespace App\Servicios;

use App\Dominio\EmpleadoBase;
use App\Infraestructura\Factories\FactoryCalculadorSalario;
use Exception;
use RuntimeException;

/**
 * Servicio encargado de toda la lógica de cálculo de salarios.
 * (SRP: Su única responsabilidad es el cálculo salarial).
 * (DIP: Utiliza el Factory para desacoplarse de las calculadoras concretas).
 */
class ServicioCalculoSalarios
{
    public function calcularSalarioEmpleado(EmpleadoBase $empleado): float
    {
        try {
            $calculadora = FactoryCalculadorSalario::obtenerCalculadora($empleado->getTipoEmpleado());
            return $calculadora->calcularSalario(
                $empleado->getSalarioBase(),
                $empleado->getParametrosCalculoSalario()
            );
        } catch (Exception $e) {
            throw new RuntimeException("Error al calcular salario para empleado ID " . $empleado->getId() . ": " . $e->getMessage(), 0, $e);
        }
    }

    public function calcularSalariosMultiples(array $empleados): array
    {
        $salarios = [];
        foreach ($empleados as $empleado) {
            if ($empleado instanceof EmpleadoBase) {
                $salarios[$empleado->getId()] = $this->calcularSalarioEmpleado($empleado);
            }
        }
        return $salarios;
    }

    public function obtenerResumenNomina(array $empleados): array
    {
        $salarios = $this->calcularSalariosMultiples($empleados);
        
        $totalConSalario = count($salarios);
        if ($totalConSalario === 0) {
            return [
                'totalEmpleados' => count($empleados),
                'empleadosConSalario' => 0,
                'salarioTotal' => 0, 'salarioPromedio' => 0,
                'salarioMinimo' => 0, 'salarioMaximo' => 0
            ];
        }
        
        return [
            'totalEmpleados' => count($empleados),
            'empleadosConSalario' => $totalConSalario,
            'salarioTotal' => array_sum($salarios),
            'salarioPromedio' => array_sum($salarios) / $totalConSalario,
            'salarioMinimo' => min($salarios),
            'salarioMaximo' => max($salarios)
        ];
    }
}