<?php

namespace PbdKn\ContaoCohCanvasBundle\ContaoManager;

use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use PbdKn\ContaoCohCanvasBundle\ContaoCohCanvasBundle;
use Contao\CoreBundle\ContaoCoreBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            [ 'name' => ContaoCohCanvasBundle::class, 'replace' => false ]
        ];
    }
}
