<?php

namespace App\Contratos;

use RuntimeException;

/**
 * Interface para cualquier clase que envíe una notificación.
 * Define el contrato para los diferentes canales de notificación (Email, SMS, etc.).
 */
interface ServicioNotificacionInterface
{
    /**
     * Envía una notificación a un destinatario.
     *
     * @param string $destinatario La dirección o número del destinatario (ej: 'user@example.com', '+1234567890').
     * @param string $mensaje El contenido del mensaje a enviar.
     * @return bool Devuelve true si la notificación se envió correctamente.
     * @throws RuntimeException Si ocurre un error durante el envío.
     */
    public function enviarNotificacion(string $destinatario, string $mensaje): bool;
}