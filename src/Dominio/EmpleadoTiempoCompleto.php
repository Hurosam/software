<?php

namespace App\Dominio;

use InvalidArgumentException;

/**
 * Representa a un empleado de tiempo completo con un bono anual.
 */
class EmpleadoTiempoCompleto extends EmpleadoBase
{
    private float $bonoAnual;

    public function __construct(
        int $id,
        string $nombre,
        string $apellido,
        string $email,
        float $salarioBase,
        float $bonoAnual = 0
    ) {
        parent::__construct($id, $nombre, $apellido, $email, $salarioBase);
        
        if ($bonoAnual < 0) {
            throw new InvalidArgumentException("El bono anual no puede ser negativo.");
        }
        
        $this->bonoAnual = $bonoAnual;
    }

    public function getTipoEmpleado(): string
    {
        return "Tiempo Completo";
    }

    public function getParametrosCalculoSalario(): array
    {
        return ['bonoAnual' => $this->bonoAnual];
    }

    public function getBonoAnual(): float
    {
        return $this->bonoAnual;
    }
}