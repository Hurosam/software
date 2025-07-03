<?php

namespace App\Infraestructura\Persistencia;

use App\Contratos\RepositorioEmpleadoInterface;
use App\Dominio\EmpleadoBase;
use Exception;
use PDO;

/**
 * Implementación del repositorio que se conectaría a una base de datos real (ej. MySQL).
 * Esta es una versión SIMULADA para demostrar la estructura.
 */
class RepositorioEmpleadoBaseDatos implements RepositorioEmpleadoInterface
{
    private PDO $pdo;

    public function __construct(string $dsn, string $usuario, string $password)
    {
        try {
            // En una aplicación real, se establecería la conexión a la BD aquí.
            // $this->pdo = new PDO($dsn, $usuario, $password);
            // $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            error_log("SIMULACIÓN: Conexión a base de datos establecida.");
        } catch (Exception $e) {
            throw new Exception("No se pudo conectar a la base de datos: " . $e->getMessage());
        }
    }

    public function guardarEmpleado(EmpleadoBase $empleado): bool
    {
        error_log("SIMULACIÓN BD: Guardando empleado ID " . $empleado->getId());
        // Lógica SQL de INSERT o UPDATE iría aquí.
        // Ejemplo: $stmt = $this->pdo->prepare("INSERT INTO empleados (...) VALUES (...)");
        return true; // Simula éxito.
    }

    public function obtenerEmpleadoPorId(int $id): ?EmpleadoBase
    {
        error_log("SIMULACIÓN BD: Obteniendo empleado por ID " . $id);
        // Lógica SQL de SELECT ... WHERE id = ? iría aquí.
        // Se necesitaría un "mapper" para convertir la fila de la BD en un objeto Empleado.
        return null; // Simula que no se encontró para esta demo.
    }

    public function obtenerTodosLosEmpleados(): array
    {
        error_log("SIMULACIÓN BD: Obteniendo todos los empleados.");
        // Lógica SQL de SELECT * FROM empleados iría aquí.
        return []; // Simula una lista vacía para esta demo.
    }
}