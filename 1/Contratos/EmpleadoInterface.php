<?php

namespace App\Contratos;

/**
 * Contrato base para cualquier empleado.
 * Define la estructura mínima y común que toda clase de empleado debe tener.
 * Esto asegura consistencia y cumple con el Principio de Sustitución de Liskov.
 */
interface EmpleadoInterface
{
    /**
     * Devuelve el ID único del empleado.
     */
    public function obtenerId(): int;

    /**
     * Devuelve el nombre completo del empleado.
     */
    public function obtenerNombre(): string;
}