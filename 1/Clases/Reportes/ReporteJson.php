<?php
namespace App\Clases\Reportes;

use App\Contratos\ReporteInterface;

/**
 * Genera un reporte en formato JSON.
 */
class ReporteJson implements ReporteInterface
{
    public function generar(array $empleados): string {
        $datos = array_map(
            fn($e) => ['id' => $e->obtenerId(), 'nombre' => $e->obtenerNombre()],
            $empleados
        );
        return json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}