<?php
namespace Application\Ports\Output;
use Domain\Entities\Receta;

interface RecetaRepositoryPort { public function guardarReceta(Receta $receta): void; }
interface ValidadorColegiaturaPort { public function validarColegiatura(string $cmp, string $dni): bool; }
interface FirmaDigitalPort { public function firmarDocumento(Receta $receta): string; }
interface SyncFarmaciaPort { public function enviarAB2B(string $docFirmado): bool; }
interface NotificacionAppPort { public function enviarTokenYQR(string $token, string $qr, string $pacienteId): void; }