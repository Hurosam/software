<?php

namespace App\Infraestructura\Persistencia;

use App\Contratos\RepositorioEmpleadoInterface;
use App\Dominio\EmpleadoBase;
use ReflectionClass;
use Exception;

/**
 * Implementación del repositorio que guarda los datos en un array en memoria.
 * Ideal para demos, prototipos y pruebas unitarias.
 */
class RepositorioEmpleadoMemoria implements RepositorioEmpleadoInterface
{
    /** @var EmpleadoBase[] */
    private array $empleados = [];
    private int $siguienteId = 1;

    public function guardarEmpleado(EmpleadoBase $empleado): bool
    {
        try {
            // Usamos Reflection para modificar el ID si es un empleado nuevo.
            $reflection = new ReflectionClass($empleado);
            $idProperty = $reflection->getProperty('id');
            $idProperty->setAccessible(true); // Permite modificar propiedades protegidas
            
            // --- ESTA ES LA SECCIÓN CORREGIDA ---
            // Si el ID es 0, significa que es un nuevo empleado y le asignamos un ID.
            if ($idProperty->getValue($empleado) === 0) {
                $idProperty->setValue($empleado, $this->siguienteId++);
            }

            // Guardamos o actualizamos el empleado en el array usando su ID como clave.
            $this->empleados[$empleado->getId()] = $empleado;
            return true;

        } catch (Exception $e) {
            error_log("Error al guardar empleado en memoria: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerEmpleadoPorId(int $id): ?EmpleadoBase
    {
        return $this->empleados[$id] ?? null;
    }

    public function obtenerTodosLosEmpleados(): array
    {
        // Devuelve los empleados como un array indexado numéricamente.
        return array_values($this->empleados);
    }

    /**
     * Método de utilidad para limpiar los datos (muy útil en tests).
     */
    public function limpiarDatos(): void
    {
        $this->empleados = [];
        $this->siguienteId = 1;
    }
}