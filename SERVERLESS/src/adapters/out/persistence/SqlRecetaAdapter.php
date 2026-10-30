<?php
namespace Adapters\Out\Persistence;

use Application\Ports\Output\RecetaRepositoryPort;
use Domain\Entities\Receta;
use Infrastructure\Configuration\Database;

class SqlRecetaAdapter implements RecetaRepositoryPort {
    public function guardarReceta(Receta $receta): void {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO recetas (id_receta, paciente_id, token, estado) VALUES (?, ?, ?, ?)");
        // $stmt->execute([$receta->idReceta, $receta->pacienteId, $receta->tokenValidacion, $receta->estado]);
    }
}