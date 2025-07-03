<?php

namespace App\Contratos;

use App\Dominio\EmpleadoBase;

/**
 * Interface para la capa de persistencia de datos de empleados.
 * Abstrae el origen de los datos (BD, memoria, API, etc.).
 */
interface RepositorioEmpleadoInterface
{
    /**
     * Guarda o actualiza un empleado en el sistema de almacenamiento.
     *
     * @param EmpleadoBase $empleado El objeto empleado a guardar.
     * @return bool Devuelve true si la operación fue exitosa, false en caso contrario.
     */
    public function guardarEmpleado(EmpleadoBase $empleado): bool;
    
    /**
     * Busca y devuelve un empleado por su ID.
     *
     * @param int $id El ID del empleado a buscar.
     * @return EmpleadoBase|null Devuelve el objeto EmpleadoBase o null si no se encuentra.
     */
    public function obtenerEmpleadoPorId(int $id): ?EmpleadoBase;
    
    /**
     * Devuelve una lista de todos los empleados en el sistema.
     *
     * @return array Un array de objetos EmpleadoBase.
     */
    public function obtenerTodosLosEmpleados(): array;
}