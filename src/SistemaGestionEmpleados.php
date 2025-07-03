<?php

namespace App;

use App\Contratos\RepositorioEmpleadoInterface;
use App\Contratos\ServicioNotificacionInterface;
use App\Contratos\CalculadorSalarioInterface;
use App\Contratos\GeneradorReporteInterface;
use App\Dominio\EmpleadoBase;
use App\Infraestructura\Factories\FactoryCalculadorSalario;
use App\Infraestructura\Factories\FactoryGeneradorReporte;
use App\Servicios\ServicioCalculoSalarios;
use App\Servicios\ServicioGeneracionReportes;
use App\Servicios\ServicioGestionEmpleados;
use App\Servicios\ServicioProcesadorPagos;
use InvalidArgumentException;

/**
 * Clase principal que orquesta todo el sistema (Patrón Facade).
 * Proporciona una interfaz simple y unificada para realizar operaciones complejas,
 * ocultando la complejidad de la interacción entre los diferentes servicios.
 */
class SistemaGestionEmpleados
{
    private ServicioGestionEmpleados $servicioGestion;
    private ServicioCalculoSalarios $servicioSalarios;
    private ServicioGeneracionReportes $servicioReportes;
    private ServicioProcesadorPagos $servicioPagos;

    public function __construct(
        RepositorioEmpleadoInterface $repositorio,
        ServicioNotificacionInterface $servicioNotificacion
    ) {
        // Inicializar las factories con sus implementaciones por defecto.
        FactoryCalculadorSalario::inicializarCalculadorasDefecto();
        FactoryGeneradorReporte::inicializarGeneradoresDefecto();

        // Componer los servicios, inyectando las dependencias necesarias.
        $this->servicioGestion = new ServicioGestionEmpleados($repositorio);
        $this->servicioSalarios = new ServicioCalculoSalarios();
        $this->servicioReportes = new ServicioGeneracionReportes();
        $this->servicioPagos = new ServicioProcesadorPagos($servicioNotificacion, $this->servicioSalarios);
    }

    // --- Métodos de la Fachada ---

    public function registrarEmpleado(EmpleadoBase $empleado): bool
    {
        return $this->servicioGestion->registrarEmpleado($empleado);
    }

    public function obtenerEmpleado(int $id): ?EmpleadoBase
    {
        return $this->servicioGestion->obtenerEmpleado($id);
    }

    public function obtenerTodosLosEmpleados(): array
    {
        return $this->servicioGestion->obtenerTodosLosEmpleados();
    }

    public function calcularSalarioEmpleado(int $idEmpleado): float
    {
        $empleado = $this->obtenerEmpleado($idEmpleado);
        if (!$empleado) {
            throw new InvalidArgumentException("No se encontró un empleado con el ID: {$idEmpleado}");
        }
        return $this->servicioSalarios->calcularSalarioEmpleado($empleado);
    }

    public function generarReporte(string $formato): string
    {
        $empleados = $this->obtenerTodosLosEmpleados();
        return $this->servicioReportes->generarReporte($empleados, $formato);
    }

    public function procesarPagoEmpleado(int $idEmpleado): array
    {
        $empleado = $this->obtenerEmpleado($idEmpleado);
        if (!$empleado) {
            throw new InvalidArgumentException("No se encontró un empleado con el ID: {$idEmpleado}");
        }
        return $this->servicioPagos->procesarPagoEmpleado($empleado);
    }
    
    public function procesarNominaCompleta(): array
    {
        $empleados = $this->obtenerTodosLosEmpleados();
        $resultados = ['pagosExitosos' => [], 'errores' => []];
        foreach ($empleados as $empleado) {
            try {
                $resultados['pagosExitosos'][] = $this->servicioPagos->procesarPagoEmpleado($empleado);
            } catch (\Exception $e) {
                $resultados['errores'][] = "Error con empleado ID {$empleado->getId()}: " . $e->getMessage();
            }
        }
        return $resultados;
    }

    public function obtenerResumenNomina(): array
    {
        $empleados = $this->obtenerTodosLosEmpleados();
        return $this->servicioSalarios->obtenerResumenNomina($empleados);
    }

    // --- Métodos para Extensibilidad (OCP) ---

    public function registrarTipoEmpleado(string $tipo, CalculadorSalarioInterface $calculadora): void
    {
        FactoryCalculadorSalario::registrarCalculadora($tipo, $calculadora);
    }

    public function registrarFormatoReporte(string $formato, GeneradorReporteInterface $generador): void
    {
        FactoryGeneradorReporte::registrarGenerador($formato, $generador);
    }
}