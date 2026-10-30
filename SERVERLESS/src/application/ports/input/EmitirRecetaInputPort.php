<?php
namespace Application\Ports\Input;
use Domain\Entities\Receta;

interface EmitirRecetaInputPort {
    public function emitir(array $datos): Receta;
}