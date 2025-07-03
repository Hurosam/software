<?php

namespace App\Servicios\Notificaciones;

use App\Contratos\ServicioNotificacionInterface;
use InvalidArgumentException;
use RuntimeException;

/**
 * Estrategia para enviar notificaciones a través de SMS.
 */
class ServicioNotificacionSMS implements ServicioNotificacionInterface
{
    private string $apiKey;
    private string $urlAPI;

    public function __construct(string $apiKey, string $urlAPI)
    {
        // En una aplicación real, aquí se guardaría la configuración para la API del proveedor de SMS.
        $this->apiKey = $apiKey;
        $this->urlAPI = $urlAPI;
    }

    public function enviarNotificacion(string $destinatario, string $mensaje): bool
    {
        if (!$this->validarNumeroTelefono($destinatario)) {
            throw new InvalidArgumentException("El número de teléfono '{$destinatario}' no es válido para el servicio de SMS.");
        }

        if (empty(trim($mensaje))) {
            throw new InvalidArgumentException("El mensaje de la notificación no puede estar vacío.");
        }

        if (strlen($mensaje) > 160) {
            throw new InvalidArgumentException("El mensaje SMS no puede exceder los 160 caracteres.");
        }

        try {
            // Simulación del envío de SMS.
            // En una implementación real, aquí se usaría cURL o Guzzle para llamar a la API externa.
            $exito = $this->simularEnvioSMS($destinatario, $mensaje);
            
            if (!$exito) {
                throw new RuntimeException("La API de SMS reportó un error al enviar a: {$destinatario}");
            }

            error_log("SIMULACIÓN: SMS enviado a {$destinatario} | Mensaje: '{$mensaje}'");
            return true;

        } catch (Exception $e) {
            throw new RuntimeException("Error en el servicio de SMS: " . $e->getMessage());
        }
    }

    private function validarNumeroTelefono(string $numero): bool
    {
        // Validación simple para números de teléfono internacionales.
        return (bool)preg_match('/^\+?[1-9]\d{1,14}$/', $numero);
    }

    private function simularEnvioSMS(string $destinatario, string $mensaje): bool
    {
        // Simplemente devolvemos true para la demostración.
        return true;
    }
}