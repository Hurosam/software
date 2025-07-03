<?php

namespace App\Dominio;

use DateTime;
use InvalidArgumentException;

/**
 * Clase base abstracta para todos los empleados.
 * Define las propiedades y comportamientos comunes.
 * (SRP: Solo maneja datos básicos del empleado)
 * (LSP: Sirve como base sustituible para todos los tipos de empleados)
 */
abstract class EmpleadoBase
{
    protected int $id;
    protected string $nombre;
    protected string $apellido;
    protected string $email;
    protected float $salarioBase;
    protected DateTime $fechaContratacion;

    public function __construct(
        int $id,
        string $nombre,
        string $apellido,
        string $email,
        float $salarioBase
    ) {
        $this->validarDatos($id, $nombre, $apellido, $email, $salarioBase);
        
        $this->id = $id;
        $this->nombre = trim($nombre);
        $this->apellido = trim($apellido);
        $this->email = strtolower(trim($email));
        $this->salarioBase = $salarioBase;
        $this->fechaContratacion = new DateTime();
    }

    // --- Getters ---
    public function getId(): int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getApellido(): string { return $this->apellido; }
    public function getEmail(): string { return $this->email; }
    public function getSalarioBase(): float { return $this->salarioBase; }
    public function getFechaContratacion(): DateTime { return $this->fechaContratacion; }
    
    public function getNombreCompleto(): string
    {
        return $this->nombre . ' ' . $this->apellido;
    }

    // --- Métodos Abstractos (deben ser implementados por las clases hijas) ---
    abstract public function getTipoEmpleado(): string;
    abstract public function getParametrosCalculoSalario(): array;

    /**
     * Valida los datos de entrada del constructor.
     * (DRY: Centraliza la lógica de validación)
     */
    private function validarDatos(int $id, string $nombre, string $apellido, string $email, float $salarioBase): void
    {
        // Permitimos ID 0 para nuevos empleados que serán guardados por el repositorio en memoria
        if ($id < 0) {
            throw new InvalidArgumentException("El ID no puede ser negativo.");
        }
        if (empty(trim($nombre))) {
            throw new InvalidArgumentException("El nombre no puede estar vacío.");
        }
        if (empty(trim($apellido))) {
            throw new InvalidArgumentException("El apellido no puede estar vacío.");
        }
        if (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("El email '{$email}' no tiene un formato válido.");
        }
        if ($salarioBase < 0) {
            throw new InvalidArgumentException("El salario base no puede ser negativo.");
        }
    }
}