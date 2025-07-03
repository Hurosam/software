<?php

namespace App\Servicios\Notificaciones;

use App\Contratos\ServicioNotificacionInterface;
use Exception;
use RuntimeException;

/**
 * Patrón Composite: Permite tratar múltiples servicios de notificación como si fueran uno solo.
 * Intenta enviar una notificación a través de todos los canales configurados.
 */
class NotificadorCompuesto implements ServicioNotificacionInterface
{
    /** @var ServicioNotificacionInterface[] */
    private array $servicios = [];

    /**
     * Agrega un servicio de notificación a la lista.
     * (OCP: Permite agregar nuevos canales sin modificar esta clase).
     */
    public function agregarServicio(ServicioNotificacionInterface $servicio): void
    {
        $this->servicios[] = $servicio;
    }

    /**
     * Envía una notificación a través de todos los servicios agregados.
     * Se considera exitoso si al menos uno de los servicios funciona.
     */
    public function enviarNotificacion(string $destinatario, string $mensaje): bool
    {
        if (empty($this->servicios)) {
            throw new RuntimeException("No hay servicios de notificación configurados en el notificador compuesto.");
        }

        $exitos = 0;
        $errores = [];

        foreach ($this->servicios as $servicio) {
            try {
                // Intentamos enviar la notificación. Si la clase de servicio considera que
                // el destinatario no es válido para ella (ej. un email para el servicio SMS),
                // lanzará una InvalidArgumentException, que capturamos y registramos.
                if ($servicio->enviarNotificacion($destinatario, $mensaje)) {
                    $exitos++;
                }
            } catch (Exception $e) {
                // Guardamos el mensaje de error para posible logging.
                $errores[] = get_class($servicio) . ": " . $e->getMessage();
            }
        }

        // Si al menos un servicio tuvo éxito, consideramos la operación exitosa.
        if ($exitos > 0) {
            return true;
        }

        // Si todos fallaron, lanzamos una excepción con un resumen de los errores.
        throw new RuntimeException("Todos los servicios de notificación fallaron. Errores: " . implode(" | ", $errores));
    }
}