<?php

namespace App\Servicios;

use App\Contratos\ServicioNotificacionInterface;
use App\Dominio\EmpleadoBase;
use DateTime;
use Exception;
use RuntimeException;

/**
 * Servicio que orquesta el proceso de pago de un empleado, incluyendo el cálculo
 * de su salario y el envío de la notificación correspondiente.
 * (SRP: Su responsabilidad es el "proceso de pago" en su conjunto).
 */
class ServicioProcesadorPagos
{
    private ServicioNotificacionInterface $servicioNotificacion;
    private ServicioCalculoSalarios $servicioSalarios;

    public function __construct(
        ServicioNotificacionInterface $servicioNotificacion,
        ServicioCalculoSalarios $servicioSalarios
    ) {
        $this->servicioNotificacion = $servicioNotificacion;
        $this->servicioSalarios = $servicioSalarios;
    }

    public function procesarPagoEmpleado(EmpleadoBase $empleado): array
    {
        try {
            // 1. Calcular el salario.
            $salarioCalculado = $this->servicioSalarios->calcularSalarioEmpleado($empleado);
            
            // 2. Simular el procesamiento del pago.
            $fechaPago = new DateTime();
            $numeroTransaccion = 'TXN-' . $empleado->getId() . '-' . $fechaPago->format('YmdHis');
            
            // 3. Preparar la información del resultado.
            $infoPago = [
                'empleadoId' => $empleado->getId(),
                'nombreEmpleado' => $empleado->getNombreCompleto(),
                'salarioCalculado' => $salarioCalculado,
                'fechaPago' => $fechaPago->format('Y-m-d H:i:s'),
                'numeroTransaccion' => $numeroTransaccion,
                'estado' => 'PROCESADO'
            ];

            // 4. Enviar notificación.
            $mensaje = sprintf(
                "Su pago ha sido procesado. Monto: $%.2f. Transacción: %s.",
                $salarioCalculado,
                $numeroTransaccion
            );

            // Intentamos enviar la notificación y registramos el resultado.
            try {
                $destinatario = $empleado->getEmail(); // Por defecto, usamos el email.
                $this->servicioNotificacion->enviarNotificacion($destinatario, $mensaje);
                $infoPago['notificacionEnviada'] = true;
            } catch (Exception $e) {
                error_log("Error enviando notificación para el pago del empleado ID " . $empleado->getId() . ": " . $e->getMessage());
                $infoPago['notificacionEnviada'] = false;
                $infoPago['errorNotificacion'] = $e->getMessage();
            }

            return $infoPago;

        } catch (Exception $e) {
            throw new RuntimeException("Error procesando el pago para el empleado ID " . $empleado->getId() . ": " . $e->getMessage(), 0, $e);
        }
    }
}