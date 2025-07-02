<?php
namespace App\Conexion;

use PDO;
use PDOException;

/**
 * Clase dedicada exclusivamente a establecer la conexión con la base de datos.
 * Aplica el Principio de Responsabilidad Única.
 */
class Conexion
{
    /**
     * Intenta conectar a la base de datos MySQL y devuelve el objeto PDO.
     * 
     * @return PDO|null Retorna el objeto PDO si la conexión es exitosa, o null si falla.
     */
    public function conectar(): ?PDO
    {
        // Parámetros de conexión. Se recomienda usar variables de entorno en un proyecto real.
        $host = 'localhost';
        $dbName = 'nomina_db';
        $user = 'root';
        $password = 'dsrhcancer1'; // La contraseña por defecto de Laragon para root es vacía.

        try {
            $dsn = "mysql:host={$host};dbname={$dbName};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $password);

            // Configuramos PDO para que lance excepciones en caso de error.
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            return $pdo;
        } catch (PDOException $e) {
            // En caso de error, lo registramos y devolvemos null para que la aplicación
            // pueda manejarlo de forma segura.
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            return null;
        }
    }
}