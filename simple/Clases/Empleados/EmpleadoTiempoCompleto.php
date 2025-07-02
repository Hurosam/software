<?php
namespace App\Clases\Empleados;

use App\Contratos\PagableInterface;

/**
 * Implementación para empleados de tiempo completo.
 * Cumple con el contrato PagableInterface.
 */
class EmpleadoTiempoCompleto extends EmpleadoBase implements PagableInterface
{
    public function __construct(int $id, string $nombre, private float $salario) {
        parent::__construct($id, $nombre);
    }

    public function calcularSalario(): float {
        return $this->salario;
    }
}