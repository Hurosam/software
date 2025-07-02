<?php
namespace App\Servicios;

// Importamos los CONTRATOS de los que vamos a depender, no las clases concretas.
use App\Contratos\RepositorioEmpleadosInterface;
use App\Contratos\NotificadorInterface;
use App\Contratos\PagableInterface;

/**
 * Servicio de alto nivel que orquesta el proceso de pago de la nómina.
 * 
 * Su única responsabilidad (SRP) es coordinar la búsqueda, cálculo y notificación.
 * No sabe cómo se busca en la BD, ni cómo se calcula un salario, ni cómo se notifica.
 *
 * Depende de abstracciones (interfaces), no de clases concretas (DIP),
 * lo que lo hace flexible y fácil de probar.
 */
class ServicioNomina
{
    private RepositorioEmpleadosInterface $repositorio;
    private NotificadorInterface $notificador;

    /**
     * El constructor recibe las dependencias (Inyección de Dependencias).
     * Esto permite que le pasemos cualquier repositorio o notificador que
     * cumpla con el contrato.
     */
    public function __construct(
        RepositorioEmpleadosInterface $repositorio,
        NotificadorInterface $notificador
    ) {
        $this->repositorio = $repositorio;
        $this->notificador = $notificador;
    }

    /**
     * Procesa el pago de un solo empleado por su ID.
     * Devuelve un string con el resumen de la operación.
     */
    public function procesarPago(int $idEmpleado): string
    {
        // El manejo de excepciones hace el código más robusto.
        try {
            // 1. Pide al repositorio que busque al empleado.
            $empleado = $this->repositorio->buscarPorId($idEmpleado);

            // Validación de datos.
            if (!$empleado) {
                return "Error: Empleado con ID {$idEmpleado} no encontrado.";
            }

            // Verificamos si el empleado es "pagable" usando la interfaz específica.
            if (!$empleado instanceof PagableInterface) {
                return "Error: El empleado '{$empleado->obtenerNombre()}' no es pagable.";
            }
            
            // 2. Calcula el salario (el objeto empleado sabe cómo hacerlo).
            $salario = $empleado->calcularSalario();
            $nombre = $empleado->obtenerNombre();

            // 3. Usa el notificador para enviar la confirmación.
            $mensajeNotificacion = "Tu pago de {$salario} ha sido procesado.";
            $resultadoNotificacion = $this->notificador->enviar($empleado, $mensajeNotificacion);

            // 4. Prepara un resumen de la operación.
            $resumen = "Pago procesado para {$nombre}.\n";
            $resumen .= "Salario: {$salario}\n";
            $resumen .= "Notificación: {$resultadoNotificacion}";
            
            return $resumen;

        } catch (\Exception $e) {
            // Captura cualquier otro error inesperado.
            return "Error crítico al procesar el pago: " . $e->getMessage();
        }
    }
}