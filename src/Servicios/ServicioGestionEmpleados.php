<?php

namespace App\Servicios;

use App\Contratos\RepositorioEmpleadoInterface;
use App\Dominio\EmpleadoBase;
use InvalidArgumentException;
use Exception;

/**
 * Servicio encargado de la lógica de negocio relacionada con la gestión de empleados (CRUD).
 * (SRP: Su única responsabilidad es gestionar empleados, no calcular salarios ni enviar notificaciones).
 * (DIP: Depende de la abstracción IRepositorioEmpleado, no de una implementación concreta).
 */
class ServicioGestionEmpleados
{
    private RepositorioEmpleadoInterface $repositorio;

    public function __construct(RepositorioEmpleadoInterface $repositorio)
    {
        $this->repositorio = $repositorio;
    }

    /**
     * Registra un nuevo empleado en el sistema.
     *
     * @param EmpleadoBase $empleado El empleado a registrar.
     * @return bool True si se registró correctamente.
     * @throws InvalidArgumentException Si el empleado ya existe (mismo email).
     * @throws Exception Si ocurre otro error al guardar.
     */
    public function registrarEmpleado(EmpleadoBase $empleado): bool
    {
        try {
            // Regla de negocio: No permitir empleados con el mismo email.
            $empleados = $this->repositorio->obtenerTodosLosEmpleados();
            foreach ($empleados as $emp) {
                if ($emp->getEmail() === $empleado->getEmail() && $emp->getId() !== $empleado->getId()) {
                    throw new InvalidArgumentException("Ya existe un empleado con el email: " . $empleado->getEmail());
                }
            }

            return $this->repositorio->guardarEmpleado($empleado);
        } catch (Exception $e) {
            error_log("Error al registrar empleado: " . $e->getMessage());
            // Re-lanzamos la excepción para que la capa superior la maneje.
            throw $e;
        }
    }

    public function obtenerEmpleado(int $id): ?EmpleadoBase
    {
        if ($id <= 0) {
            throw new InvalidArgumentException("El ID del empleado debe ser un número positivo.");
        }
        return $this->repositorio->obtenerEmpleadoPorId($id);
    }

    public function obtenerTodosLosEmpleados(): array
    {
        return $this->repositorio->obtenerTodosLosEmpleados();
    }
}