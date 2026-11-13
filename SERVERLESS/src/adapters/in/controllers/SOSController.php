<?php
// Permitir peticiones desde cualquier origen (útil en testing local)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// Incluir la clase de conexión
require_once '../../../config/Conexion.php';

// Capturar los datos enviados por POST en formato JSON
$data = json_decode(file_get_contents("php://input"));

// Extraer descripción (si no viene, se asigna string vacío)
$descripcion = isset($data->descripcion) ? $data->descripcion : 'Sin descripción';

// Generar un Ticket único para el HU1
$ticket_generado = "TKT-" . rand(100000, 999999);

try {
    // 1. Instanciar la base de datos
    $baseDeDatos = new Conexion();
    $db = $baseDeDatos->conectar();

    // 2. Preparar la consulta SQL
    $query = "INSERT INTO alertas_sos (ticket_id, descripcion, estado) VALUES (:ticket, :desc, :estado)";
    $stmt = $db->prepare($query);

    // 3. Vincular los parámetros de forma segura (previene Inyección SQL)
    $estado_inicial = "Encolado";
    $stmt->bindParam(":ticket", $ticket_generado);
    $stmt->bindParam(":desc", $descripcion);
    $stmt->bindParam(":estado", $estado_inicial);

    // 4. Ejecutar y responder al Frontend
    if($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Alerta SOS registrada correctamente.",
            "data" => [
                "ticket" => $ticket_generado,
                "estado" => $estado_inicial
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "No se pudo registrar la alerta."]);
    }

} catch (Exception $e) {
    echo json_encode([
        "success" => false, 
        "message" => "Error interno del servidor.", 
        "error" => $e->getMessage()
    ]);
}
?>