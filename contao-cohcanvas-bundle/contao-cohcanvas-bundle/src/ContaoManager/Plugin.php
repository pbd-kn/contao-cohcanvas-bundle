<?php

namespace PbdKn\ContaoCohCanvasBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use PbdKn\ContaoCohCanvasBundle\ContaoCohCanvasBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(ContaoCohCanvasBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class])
        ];
    }
}
