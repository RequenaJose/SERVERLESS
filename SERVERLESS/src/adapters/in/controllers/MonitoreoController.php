<?php
header('Content-Type: application/json');
// require_once __DIR__ . '/../../../../vendor/autoload.php';

use Application\UseCases\ObtenerTelemetriaUseCase;

$ticketId = $_GET['ticket'] ?? 'TICKET-84920';

$useCase = new ObtenerTelemetriaUseCase();
$datosMonitoreo = $useCase->ejecutar($ticketId);

// Respuesta optimizada para baja latencia (< 1 segundo)
echo json_encode(['success' => true, 'data' => $datosMonitoreo]);