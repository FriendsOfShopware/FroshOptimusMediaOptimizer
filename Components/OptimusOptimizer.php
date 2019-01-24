<?php

namespace FroshOptimusMediaOptimizer\Components;

use Shopware;
use Shopware\Bundle\MediaBundle\Optimizer\OptimizerInterface;

/**
 * Class OptimusOptimizer
 */
class OptimusOptimizer implements OptimizerInterface
{
    /**
     * @var array
     */
    public static $supportedMimeTypes = ['image/jpeg', 'image/jpg'];
    /**
     * @var OptimusService
     */
    private $optimusService;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var array
     */
    private $pluginConfig;

    /**
     * OptimusOptimizer constructor.
     *
     * @param OptimusService $optimusService
     * @param string         $rootDir
     * @param array          $pluginConfig
     */
    public function __construct(OptimusService $optimusService, $rootDir, array $pluginConfig)
    {
        $this->optimusService = $optimusService;
        $this->rootDir = $rootDir;
        $this->pluginConfig = $pluginConfig;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Optimus.io';
    }

    /**
     * @param string $filepath
     */
    public function run($filepath)
    {
        //as of SW5.3 media optimizer uses tmp-Folder
        if (!$this->isShopware53()) {
            $filepath = $this->rootDir . $filepath;
        }

        $this->optimusService->optimize($filepath);

        if ($this->pluginConfig['optimizeOriginal']) {
            $this->optimizeOriginalFiles();
        }
    }

    /**
     * @return array
     */
    public function getSupportedMimeTypes()
    {
        return self::$supportedMimeTypes;
    }

    /**
     * @throws \Zend_Cache_Exception
     *
     * @return bool
     */
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

    /**
     * Check if current environment is shopware 5.
     *
     * @return bool
     */
    public function isShopware53()
    {
        return version_compare(Shopware::VERSION, '5.3.0', '>=');
    }

    private function optimizeOriginalFiles()
    {
        $sql = "SELECT * FROM s_media where albumID<>-13 AND type='IMAGE' and path not like('%thumb_export%') and extension in('jpg') and userID<>-1 ORDER by id DESC LIMIT 0,10";

        $mediaResource = \Shopware\Components\Api\Manager::getResource('media');
        $mediaservice = Shopware()->Container()->get('shopware_media.media_service');

        foreach (Shopware()->Db()->fetchAll($sql) as $media) {
            $mediainfo = $mediaResource->getOne($media['id']);

            $path = explode('/media', $mediainfo['path']);
            $localfilepath = $this->rootDir . '/media' . $path[1];

            $origFilesize = $mediaservice->getSize($mediainfo['path']);
            $masse = getimagesize($mediainfo['path']);
            $breite = (int) $masse[0];
            $hoehe = (int) $masse[1];

            if ($origFilesize > 0) {
                /*
                 * TODO: move this to optimusService
                 * + make images smaller automatically
                */
                if (($origFilesize / 1024) < 5000 && $breite < 10000 && $hoehe < 10000 && $breite > 0 && $hoehe > 0) {
                    if ($mediaservice->getAdapterType() === 'local') {
                        $filepath = $localfilepath;
                    } else {
                        $file = tmpfile();
                        $filepath = stream_get_meta_data($file)['uri'];
                        file_put_contents($filepath, $mediaservice->read($mediainfo['path']));
                    }

                    try {
                        $this->optimusService->optimize($filepath);
                        $filesize = filesize($filepath);
                        Shopware()->Db()->query('UPDATE s_media SET file_size=?,userID=-1 WHERE id=?',
                            [$filesize, $media['id']]);
                    } catch (\Exception $e) {
                    }

                    if ($mediaservice->getAdapterType() !== 'local') {
                        $mediaservice->writeStream('/media' . $path[1], $file);
                    }
                }
            }
        }

        return true;
    }
}
