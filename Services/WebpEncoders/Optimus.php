<?php

namespace FroshOptimusMediaOptimizer\Services\WebpEncoders;

use FroshOptimusMediaOptimizer\Components\OptimusService;
use FroshWebP\Components\WebpEncoderInterface;
use Shopware\Components\CacheManager;
use Shopware\Components\Plugin\ConfigReader;

class Optimus implements WebpEncoderInterface
{
    /** @var OptimusService */
    private $optimusService;

    /** @var ConfigReader */
    private $configReader;

    /** @var CacheManager */
    private $cacheManager;

    public function __construct(OptimusService $optimusService, ConfigReader $configReader, CacheManager $cacheManager)
    {
        $this->optimusService = $optimusService;
        $this->configReader = $configReader;
        $this->cacheManager = $cacheManager;
    }

    /** {@inheritdoc} */
    public function getName()
    {
        return 'optimus.io';
    }

    /** {@inheritdoc} */
    public function encode($image, $quality)
    {
        ob_start();
        imagejpeg($image);
        $contents =  ob_get_contents();
        ob_end_clean();
        imagedestroy($image);

        $file = tmpfile();
        $filepath = stream_get_meta_data($file)['uri'];

        file_put_contents($filepath, $contents);

        $this->optimusService->optimize($filepath, OptimusService::OPTION_WEBP);

        return file_get_contents($filepath);
    }

    /** {@inheritdoc} */
    public function isRunnable()
    {
        if (!$this->configReader->getByPluginName('FroshOptimusMediaOptimizer')['webp']) {
            return false;
        }

        /** @var \Zend_Cache_Core $cache */
        $cache = $this->cacheManager->getCoreCache();

        $cacheKey = md5($this->optimusService->getApiKey() . 'optimus');
        $cacheValue = $cache->load($cacheKey);

        if (!$cacheValue) {
            $cacheValue = $this->optimusService->verifyApiKey();
            $cache->save($cacheValue, $cacheKey);
        }

        return $cacheValue;
    }
}
