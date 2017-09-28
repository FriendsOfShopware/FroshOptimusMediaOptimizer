<?php

namespace TinectOptimusOptimizer\Components;

use Shopware;
use Shopware\Bundle\MediaBundle\Optimizer\OptimizerInterface;

/**
 * Class OptimusOptimizer
 * @package TinectOptimusOptimizer\Components
 */
class OptimusOptimizer implements OptimizerInterface
{
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
     * @param OptimusService $optimusService
     * @param string $rootDir
     * @param array $pluginConfig
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
        //ass of SW5.3 media optimizer uses tmp-Folder
        if (!$this->isShopware53()) {
            $filepath = $this->rootDir . $filepath;
        }

        $mime = mime_content_type($filepath);
        $hasWebPSupport = function_exists('imagecreatefromwebp');

        switch ($mime) {
            case 'image/webp':
                if (!$hasWebPSupport) {
                    continue;
                }
                if ($this->isShopware53()) {
                    $im = imagecreatefromwebp($filepath);
                    imagejpeg($im, $filepath . '.jpg', 100);
                    imagedestroy($im);

                    $this->optimusService->optimize($filepath . '.jpg',
                        'webp',
                        $filepath);
                    @unlink($filepath . '.jpg');

                } else {

                    //TODO: Test in 5.2.17
                    $jpgPath = str_replace('.webp', '.jpg', $filepath);
                    $pngPath = str_replace('.webp', '.png', $filepath);

                    if (@file_exists($jpgPath)) {
                        $this->optimusService->optimize($jpgPath,
                            'webp',
                            $filepath);
                    } elseif (@file_exists($pngPath)) {
                        $this->optimusService->optimize($pngPath,
                            'webp',
                            $filepath);
                    }

                }

                break;
            default:
                $this->optimusService->optimize($filepath);
                break;
        }
        if ($this->pluginConfig['optimizeOriginal']) {
            $this->optimizeOriginalFiles();
        }
    }


    private function optimizeOriginalFiles()
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', -1);
        set_time_limit(0);

        $sql = "SELECT * FROM s_media where albumID<>-13 AND type='IMAGE' and path not like('%thumb_export%') and extension in('jpg','png') and userID<>-1 ORDER by id DESC /* LIMIT 0,20*/";

        $mediaResource = \Shopware\Components\Api\Manager::getResource('media');

        foreach (Shopware()->Db()->fetchAll($sql) as $media) {

            $mediainfo = $mediaResource->getOne($media["id"]);
            $path = explode("/media", $mediainfo["path"]);
            $filepath = $this->rootDir . "/media" . $path[1];
            $origFilesize = @filesize($filepath);
            $masse = @getimagesize($filepath);
            $breite = $masse[0];
            $hoehe = $masse[1];

            if ($origFilesize > 0) {

                /*
                 * TODO: move this to optimusService
                 * + make images smaller automatically
                */
                if (($origFilesize / 1024) < 5000 && $breite < 10000 && $hoehe < 10000 && $breite > 0 && $hoehe > 0) {


                    try {
                        $this->optimusService->optimize($filepath);
                        $filesize = @filesize($filepath);
                        Shopware()->Db()->query("UPDATE s_media SET file_size=" . $filesize . ",userID=-1 WHERE id=" . $media["id"]);

                    } catch (\Exception $e) {
                    }

                }

            }


        }

        return true;

    }

    /**
     * @return array
     */
    public function getSupportedMimeTypes()
    {
        return ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    }

    /**
     * @return bool
     */
    public function isRunnable()
    {
        return true;
        //TODO: Implement and Cache result!
        return $this->optimusService->verifyApiKey();
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
}