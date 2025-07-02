<?php
// Su namespace es App\Conexion
namespace App\Conexion;

use PDO;
use App\Contratos\RepositorioEmpleadosInterface; // Importa la interfaz
use App\Contratos\EmpleadoInterface;
use App\Clases\Empleados\EmpleadoTiempoCompleto;
use App\Clases\Empleados\EmpleadoMedioTiempo;
use App\Clases\Empleados\Contratista;

/**
 * Implementación concreta que habla con la BD.
 * Implementa el contrato RepositorioEmpleadosInterface.
 */
class RepositorioEmpleados implements RepositorioEmpleadosInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    private function crearObjetoEmpleado(array $datos): ?EmpleadoInterface {
        switch ($datos['tipo']) {
            case 'tiempo_completo':
                return new EmpleadoTiempoCompleto($datos['id'], $datos['nombre'], $datos['salario_mensual']);
            case 'medio_tiempo':
                return new EmpleadoMedioTiempo($datos['id'], $datos['nombre'], $datos['salario_mensual']);
            case 'contratista':
                return new Contratista($datos['id'], $datos['nombre'], $datos['tarifa_por_hora'], $datos['horas_trabajadas']);
            default:
                return null;
        }
    }

    public function buscarPorId(int $id): ?EmpleadoInterface {
        $stmt = $this->pdo->prepare("SELECT * FROM empleados WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);
        return $datos ? $this->crearObjetoEmpleado($datos) : null;
    }

    public function obtenerTodos(): array {
        $stmt = $this->pdo->query("SELECT * FROM empleados ORDER BY id");
        $empleados = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $datos) {
            $empleado = $this->crearObjetoEmpleado($datos);
            if ($empleado) $empleados[] = $empleado;
        }
        return $empleados;
    }
}