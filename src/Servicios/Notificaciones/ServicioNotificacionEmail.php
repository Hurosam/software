<?php

namespace App\Servicios\Notificaciones;

use App\Contratos\ServicioNotificacionInterface;
use InvalidArgumentException;
use RuntimeException;

/**
 * Estrategia para enviar notificaciones a través de correo electrónico.
 */
class ServicioNotificacionEmail implements ServicioNotificacionInterface
{
    private string $servidorSMTP;
    private string $usuario;
    private string $password;

    public function __construct(string $servidorSMTP, string $usuario, string $password)
    {
        // En una aplicación real, aquí se guardaría la configuración del servidor de correo.
        $this->servidorSMTP = $servidorSMTP;
        $this->usuario = $usuario;
        $this->password = $password;
    }

    public function enviarNotificacion(string $destinatario, string $mensaje): bool
    {
        if (!filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("El email del destinatario '{$destinatario}' no es válido para el servicio de Email.");
        }

        if (empty(trim($mensaje))) {
            throw new InvalidArgumentException("El mensaje de la notificación no puede estar vacío.");
        }

        try {
            // Simulación del envío de email.
            // En una implementación real, aquí se usaría una librería como PHPMailer o Symfony Mailer.
            $exito = $this->simularEnvioEmail($destinatario, $mensaje);
            
            if (!$exito) {
                // Si la librería de email devolviera un error.
                throw new RuntimeException("El proveedor de correo reportó un error al enviar a: {$destinatario}");
            }

            // Para la demo, simulamos que siempre se envía con éxito.
            error_log("SIMULACIÓN: EMAIL enviado a {$destinatario} | Mensaje: '{$mensaje}'");
            return true;

        } catch (Exception $e) {
            // Re-lanzamos la excepción para que sea manejada por la capa superior.
            throw new RuntimeException("Error en el servicio de email: " . $e->getMessage());
        }
    }

    private function simularEnvioEmail(string $destinatario, string $mensaje): bool
    {
        // Simplemente devolvemos true para la demostración.
        return true;
    }
}