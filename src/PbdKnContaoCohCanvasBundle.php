<?php

namespace PbdKn\ContaoCohCanvasBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class PbdKnContaoCohCanvasBundle extends Bundle
{
    public function getContaoResourcesPath(): string
    {
        return 'Resources/contao/';
    }

    
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
