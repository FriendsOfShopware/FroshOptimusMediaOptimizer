<?php

namespace FroshOptimusMediaOptimizer\Components;

use Shopware\Components\Plugin\CachedConfigReader;
use Shopware\Components\HttpClient;

/**
 * Class OptimusServiceFactory
 */
class OptimusServiceFactory
{
    /**
     * @param CachedConfigReader $cachedConfigReader
     *
     * @param HttpClient\GuzzleFactory $guzzleFactory
     * @return OptimusService
     */
    public static function factory(CachedConfigReader $cachedConfigReader, HttpClient\GuzzleFactory $guzzleFactory)
    {
        $config = $cachedConfigReader->getByPluginName('FroshOptimusMediaOptimizer');

        $optimusLicenseKey = $config['optimusLicenseKey'];
        
        if(getenv('OPTIMUSLICENSEKEY')) {
            $optimusLicenseKey = getenv('OPTIMUSLICENSEKEY');
        }

        return new OptimusService($optimusLicenseKey, $guzzleFactory->createClient());
    }
}
