<?php

namespace App\Dominio;

use InvalidArgumentException;

/**
 * Representa a un contratista que cobra por horas.
 */
class EmpleadoContratista extends EmpleadoBase
{
    private int $horasContratadas;
    private float $tarifaPorHora;

    public function __construct(
        int $id,
        string $nombre,
        string $apellido,
        string $email,
        float $salarioBase, // En este caso, salarioBase podría ser 0
        int $horasContratadas,
        float $tarifaPorHora
    ) {
        parent::__construct($id, $nombre, $apellido, $email, $salarioBase);
        
        if ($horasContratadas <= 0) {
            throw new InvalidArgumentException("Las horas contratadas deben ser mayor a 0.");
        }
        
        if ($tarifaPorHora <= 0) {
            throw new InvalidArgumentException("La tarifa por hora debe ser mayor a 0.");
        }
        
        $this->horasContratadas = $horasContratadas;
        $this->tarifaPorHora = $tarifaPorHora;
    }

    public function getTipoEmpleado(): string
    {
        return "Contratista";
    }

    public function getParametrosCalculoSalario(): array
    {
        return [
            'horasContratadas' => $this->horasContratadas,
            'tarifaPorHora' => $this->tarifaPorHora
        ];
    }

    public function getHorasContratadas(): int
    {
        return $this->horasContratadas;
    }

    public function getTarifaPorHora(): float
    {
        return $this->tarifaPorHora;
    }
}