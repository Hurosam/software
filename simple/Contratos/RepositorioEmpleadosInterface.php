<?php
// Su namespace es App\Contratos
namespace App\Contratos;

/**
 * Contrato para cualquier clase que gestione la persistencia de datos.
 * Define las reglas, no la implementación.
 */
interface RepositorioEmpleadosInterface
{
    /**
     * Busca un empleado por su ID.
     */
    public function buscarPorId(int $id): ?EmpleadoInterface;

    /**
     * Devuelve un array con todos los empleados.
     * @return EmpleadoInterface[]
     */
    public function obtenerTodos(): array;
}