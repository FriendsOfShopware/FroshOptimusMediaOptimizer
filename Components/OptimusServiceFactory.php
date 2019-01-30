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

        $optimusLicenseKey = $config['optimusLicenseKey'];
        
        if(getenv('OPTIMUSLICENSEKEY')) {
            $optimusLicenseKey = getenv('OPTIMUSLICENSEKEY');
        }

        return new OptimusService($optimusLicenseKey);
    }
}
