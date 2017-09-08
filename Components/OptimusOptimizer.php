<?php

namespace TinectOptimusOptimizer\Components;

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
     * OptimusOptimizer constructor.
     * @param OptimusService $optimusService
     * @param $rootDir
     */
    public function __construct(OptimusService $optimusService, $rootDir)
    {
        $this->optimusService = $optimusService;
        $this->rootDir = $rootDir;
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

        switch ($mime) {
            case 'image/webp':

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
    }


    /**
     * Returns the extension of the file with passed path
     *
     * @param string
     *
     * @return string
     */
    private function getImageExtension($path)
    {
        $pathInfo = pathinfo($path);
        return $pathInfo['extension'];
    }

    /**
     * Compare versions.
     * @param string $version Like: 5.0.0
     * @param string $operator Like: <=
     *
     * @return mixed
     */
    public function versionCompare($version, $operator)
    {
        // return by default version compare
        return version_compare(Shopware()->Config()->get('Version'), $version, $operator);
    }

    /**
     * Check if current environment is shopware 5.
     *
     * @return bool
     */
    public function isShopware53()
    {
        return $this->versionCompare('5.3.0', '>=');
    }


}