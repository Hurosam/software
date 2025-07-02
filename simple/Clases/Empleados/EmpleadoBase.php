<?php
namespace App\Clases\Empleados;

use App\Contratos\EmpleadoInterface;

/**
 * Clase base abstracta para no repetir código (Principio DRY).
 * Todos los empleados comparten un ID y un nombre.
 */
abstract class EmpleadoBase implements EmpleadoInterface
{
    public function __construct(protected int $id, protected string $nombre) {}

    public function obtenerId(): int {
        return $this->id;
    }

    public function obtenerNombre(): string {
        return $this->nombre;
    }
}