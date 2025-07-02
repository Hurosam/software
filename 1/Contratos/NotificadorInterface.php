<?php

namespace App\Contratos;

/**
 * Contrato para cualquier servicio que envíe notificaciones.
 * 
 * Permite añadir nuevos métodos de notificación (SMS, Slack, etc.) sin modificar
 * el código que los usa, cumpliendo el Principio Abierto/Cerrado (OCP).
 */
interface NotificadorInterface
{
    /**
     * Envía un mensaje a un empleado.
     * Devuelve un string con el estado de la operación para logging.
     */
    public function enviar(EmpleadoInterface $empleado, string $mensaje): string;
}