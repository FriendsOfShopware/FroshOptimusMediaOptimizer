<?php

namespace FroshOptimusMediaOptimizer\Components;

use Shopware\Components\Plugin\CachedConfigReader;

/**
 * Class OptimusServiceFactory
 */
class OptimusServiceFactory
{
    /**
     * @param CachedConfigReader $cachedConfigReader
     *
     * @return OptimusService
     */
    public static function factory(CachedConfigReader $cachedConfigReader)
    {
        $config = $cachedConfigReader->getByPluginName('FroshOptimusMediaOptimizer');

        $optimusLicenseKey = getenv('OPTIMUSLICENSEKEY') ?: $config['optimusLicenseKey'];

        return new OptimusService($optimusLicenseKey);
    }
}
