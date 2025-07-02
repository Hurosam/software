<?php
namespace App\Clases\Empleados;

use App\Contratos\PagableInterface;

/**
 * Implementación para empleados de medio tiempo.
 * Demuestra la facilidad de extender el sistema (Principio Abierto/Cerrado).
 */
class EmpleadoMedioTiempo extends EmpleadoBase implements PagableInterface
{
    public function __construct(int $id, string $nombre, private float $salario) {
        parent::__construct($id, $nombre);
    }

    public function calcularSalario(): float {
        return $this->salario;
    }
}