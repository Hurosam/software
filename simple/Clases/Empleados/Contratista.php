<?php
namespace App\Clases\Empleados;

use App\Contratos\PagableInterface;

/**
 * Implementación para contratistas que cobran por horas.
 */
class Contratista extends EmpleadoBase implements PagableInterface
{
    public function __construct(int $id, string $nombre, private float $tarifaPorHora, private int $horasTrabajadas) {
        parent::__construct($id, $nombre);
    }

    public function calcularSalario(): float {
        return $this->tarifaPorHora * $this->horasTrabajadas;
    }
}