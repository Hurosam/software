<?php

namespace App\Infraestructura\Persistencia;

use App\Contratos\RepositorioEmpleadoInterface;
use App\Dominio\EmpleadoBase;
use App\Dominio\EmpleadoTiempoCompleto;
use App\Dominio\EmpleadoMedioTiempo;
use App\Dominio\EmpleadoContratista;
use PDO;
use Exception;

/**
 * Implementación REAL del repositorio que se conecta a una base de datos MySQL.
 */
class RepositorioEmpleadoMySQL implements RepositorioEmpleadoInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function guardarEmpleado(EmpleadoBase $empleado): bool
    {
        // Decide si es una inserción (ID=0) o una actualización (ID>0)
        if ($empleado->getId() === 0) {
            $sql = "INSERT INTO empleados (nombre, apellido, email, tipo, salario_base, fecha_contratacion, bono_anual, horas_semanales, horas_contratadas, tarifa_por_hora) 
                    VALUES (:nombre, :apellido, :email, :tipo, :salario_base, :fecha_contratacion, :bono_anual, :horas_semanales, :horas_contratadas, :tarifa_por_hora)";
        } else {
            // Lógica de actualización no implementada en esta demo para simplificar.
            // En una app real, aquí iría un UPDATE.
            return true;
        }
        
        $stmt = $this->pdo->prepare($sql);

        $params = $empleado->getParametrosCalculoSalario();

        return $stmt->execute([
            ':nombre' => $empleado->getNombre(),
            ':apellido' => $empleado->getApellido(),
            ':email' => $empleado->getEmail(),
            ':tipo' => $empleado->getTipoEmpleado(),
            ':salario_base' => $empleado->getSalarioBase(),
            ':fecha_contratacion' => $empleado->getFechaContratacion()->format('Y-m-d H:i:s'),
            ':bono_anual' => $params['bonoAnual'] ?? null,
            ':horas_semanales' => $params['horasSemanales'] ?? null,
            ':horas_contratadas' => $params['horasContratadas'] ?? null,
            ':tarifa_por_hora' => $params['tarifaPorHora'] ?? null
        ]);
    }

    public function obtenerEmpleadoPorId(int $id): ?EmpleadoBase
    {
        $stmt = $this->pdo->prepare("SELECT * FROM empleados WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);

        return $datos ? $this->mapearArrayAObjeto($datos) : null;
    }

    public function obtenerTodosLosEmpleados(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM empleados ORDER BY id");
        $empleados = [];
        while ($datos = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $empleados[] = $this->mapearArrayAObjeto($datos);
        }
        return $empleados;
    }

    /**
     * Convierte una fila de la base de datos en el objeto Empleado correspondiente.
     */
    private function mapearArrayAObjeto(array $datos): EmpleadoBase
    {
        switch ($datos['tipo']) {
            case 'Tiempo Completo':
                return new EmpleadoTiempoCompleto($datos['id'], $datos['nombre'], $datos['apellido'], $datos['email'], $datos['salario_base'], $datos['bono_anual']);
            case 'Medio Tiempo':
                return new EmpleadoMedioTiempo($datos['id'], $datos['nombre'], $datos['apellido'], $datos['email'], $datos['salario_base'], $datos['horas_semanales']);
            case 'Contratista':
                return new EmpleadoContratista($datos['id'], $datos['nombre'], $datos['apellido'], $datos['email'], $datos['salario_base'], $datos['horas_contratadas'], $datos['tarifa_por_hora']);
            default:
                throw new Exception("Tipo de empleado desconocido desde la BD: " . $datos['tipo']);
        }
    }
}