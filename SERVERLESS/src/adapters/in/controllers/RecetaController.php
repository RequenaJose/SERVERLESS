<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../vendor/autoload.php'; // Asumiendo uso de composer o autoload manual

use Application\UseCases\EmitirRecetaUseCase;
use Adapters\Out\Persistence\{ReniecCmpAdapter, ServicioFirmaDigitalAdapter, PlataformaB2BFarmaciaAdapter, PushNotificationAppAdapter, SqlRecetaAdapter};

try {
    $datos = json_decode(file_get_contents('php://input'), true);
    
    $useCase = new EmitirRecetaUseCase(
        new ReniecCmpAdapter(),
        new ServicioFirmaDigitalAdapter(),
        new PlataformaB2BFarmaciaAdapter(),
        new PushNotificationAppAdapter(),
        new SqlRecetaAdapter()
    );

    $receta = $useCase->emitir($datos);

    echo json_encode(['success' => true, 'data' => ['token' => $receta->tokenValidacion]]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}