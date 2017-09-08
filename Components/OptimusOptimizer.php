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
        $extension = strtolower($this->getImageExtension($filepath));

        switch ($extension) {
            case 'webp':
                $jpgPath = $this->rootDir . str_replace('.webp', '.jpg', $filepath);
                $pngPath = $this->rootDir . str_replace('.webp', '.png', $filepath);

                if (@file_exists($jpgPath)) {
                    $this->optimusService->optimize($jpgPath,
                        'webp',
                        $this->rootDir . $filepath);
                } elseif (@file_exists($pngPath)) {
                    $this->optimusService->optimize($pngPath,
                        'webp',
                        $this->rootDir . $filepath);
                }
                break;
            default:
                $this->optimusService->optimize($this->rootDir . $filepath);
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


}