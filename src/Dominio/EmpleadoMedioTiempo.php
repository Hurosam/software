<?php

namespace App\Dominio;

use InvalidArgumentException;

/**
 * Representa a un empleado de medio tiempo.
 */
class EmpleadoMedioTiempo extends EmpleadoBase
{
    private int $horasSemanales;

    public function __construct(
        int $id,
        string $nombre,
        string $apellido,
        string $email,
        float $salarioBase,
        int $horasSemanales = 20
    ) {
        parent::__construct($id, $nombre, $apellido, $email, $salarioBase);
        
        if ($horasSemanales <= 0 || $horasSemanales >= 40) {
            throw new InvalidArgumentException("Las horas semanales para un empleado de medio tiempo deben estar entre 1 y 39.");
        }
        
        $this->horasSemanales = $horasSemanales;
    }

    public function getTipoEmpleado(): string
    {
        return "Medio Tiempo";
    }

    public function getParametrosCalculoSalario(): array
    {
        return ['horasSemanales' => $this->horasSemanales];
    }

    public function getHorasSemanales(): int
    {
        return $this->horasSemanales;
    }
}