<?php
header('Content-Type: application/json');
// require_once __DIR__ . '/../../../../vendor/autoload.php'; // Habilitar si usas Composer

use Application\UseCases\RegistrarAlertaUseCase;

// Recibir JSON del frontend
$input = json_decode(file_get_contents('php://input'), true);
$ubicacion = $input['ubicacion'] ?? 'Lat: -12.068, Lng: -75.210 (Av. Giráldez 230)';
$descripcion = $input['descripcion'] ?? 'Emergencia no especificada';
$fotografias = $input['fotografias'] ?? [];

$useCase = new RegistrarAlertaUseCase();
$resultado = $useCase->ejecutar($ubicacion, $descripcion, $fotografias);

// Respuesta en menos de 2 segundos (Simulada)
echo json_encode(['success' => true, 'data' => $resultado]);