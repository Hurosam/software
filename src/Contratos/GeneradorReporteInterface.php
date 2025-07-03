<?php

namespace App\Contratos;

use RuntimeException;

/**
 * Interface para cualquier clase que genere un reporte.
 * Define el contrato para las diferentes estrategias de generación de reportes (JSON, PDF, etc.).
 */
interface GeneradorReporteInterface
{
    /**
     * Genera un reporte en un formato específico.
     *
     * @param array $datosEmpleados Un array de objetos EmpleadoBase.
     * @return string El contenido del reporte generado (ej: una cadena JSON, contenido de un PDF).
     * @throws RuntimeException Si ocurre un error durante la generación.
     */
    public function generarReporte(array $datosEmpleados): string;
}