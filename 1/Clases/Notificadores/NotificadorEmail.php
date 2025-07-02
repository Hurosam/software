<?php
namespace App\Clases\Notificadores;

use App\Contratos\NotificadorInterface;
use App\Contratos\EmpleadoInterface;

/**
 * Implementación concreta para enviar notificaciones por Email.
 */
class NotificadorEmail implements NotificadorInterface
{
    public function enviar(EmpleadoInterface $empleado, string $mensaje): string {
        $log = "EMAIL enviado a {$empleado->obtenerNombre()}: '{$mensaje}'";
        error_log($log); // Simula el envío escribiendo en el log de errores del servidor.
        return $log;
    }
}