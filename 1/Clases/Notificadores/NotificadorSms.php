<?php
namespace App\Clases\Notificadores;

use App\Contratos\NotificadorInterface;
use App\Contratos\EmpleadoInterface;

/**
 * Implementación concreta para enviar notificaciones por SMS.
 * Se puede intercambiar con NotificadorEmail sin cambiar el código que la usa.
 */
class NotificadorSms implements NotificadorInterface
{
    public function enviar(EmpleadoInterface $empleado, string $mensaje): string {
        $log = "SMS enviado a {$empleado->obtenerNombre()}: '{$mensaje}'";
        error_log($log); // Simula el envío.
        return $log;
    }
}