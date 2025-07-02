<?php

namespace App\Contratos;

/**
 * Contrato para cualquier entidad a la que se le pueda calcular un salario.
 * 
 * Se separa de EmpleadoInterface para cumplir con el Principio de Segregación
 * de Interfaces (ISP), ya que una clase no debe ser forzada a implementar
 * métodos que no va a usar.
 */
interface PagableInterface
{
    /**
     * Calcula y devuelve el salario para la entidad.
     */
    public function calcularSalario(): float;
}