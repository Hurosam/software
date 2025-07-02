<?php

namespace App\Contratos;

/**
 * Contrato para cualquier clase que genere un reporte.
 * 
 * Facilita la adición de nuevos formatos de reporte (PDF, CSV, etc.)
 * sin alterar el código existente (OCP).
 */
interface ReporteInterface
{
    /**
     * Genera un reporte a partir de una lista de empleados.
     * 
     * @param EmpleadoInterface[] $empleados
     * @return string El contenido del reporte (JSON, texto plano, etc.).
     */
    public function generar(array $empleados): string;
}