<?php
namespace TinectOptimusOptimizer\Services\WebpEncoders;
use Shopware\Components\Plugin\CachedConfigReader;
use ShyimWebP\Components\WebpEncoderInterface;
use TinectOptimusOptimizer\Components\OptimusService;

class Optimus implements WebpEncoderInterface
{
    /** @var array */
    private $optimusService;

    public function __construct(OptimusService $optimusService)
    {
        $this->optimusService = $optimusService;
    }

    /** {@inheritdoc} */
    public function getName()
    {
        return 'optimus.io';
    }

    /** {@inheritdoc} */
    public function encode($image, $quality)
    {
        $file = tmpfile();
        $filepath = stream_get_meta_data($file)['uri'];
        file_put_contents($file,$image);

        $this->optimusService->optimize($filepath, OptimusService::OPTION_WEBP);

        return file_get_contents($filepath);
    }

    /** {@inheritdoc} */
    public function isRunnable()
    {
        /** @var \Zend_Cache_Core $cache */
        $cache = Shopware()->Container()->get('shopware.cache_manager')->getCoreCache();

        $cacheKey = md5($this->optimusService->getApiKey() . 'optimus');
        $cacheValue = $cache->load($cacheKey);

        if (!$cacheValue) {
            $cacheValue = $this->optimusService->verifyApiKey();
            $cache->save($cacheValue, $cacheKey);
        }

        return $cacheValue;
    }
}