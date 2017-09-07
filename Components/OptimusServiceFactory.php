<?php

namespace TinectOptimusOptimizer\Components;

use Shopware\Components\Plugin\CachedConfigReader;

/**
 * Class OptimusServiceFactory
 * @package TinectOptimusOptimizer\Components
 */
class OptimusServiceFactory
{
    /**
     * @param CachedConfigReader $cachedConfigReader
     * @return OptimusService
     */
    public static function factory(CachedConfigReader $cachedConfigReader)
    {
        $config = $cachedConfigReader->getByPluginName('TinectOptimusOptimizer');

        return new OptimusService($config['optimusLicenseKey']);
    }
}