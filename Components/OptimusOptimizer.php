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
        $this->optimusService->optimize($this->rootDir . $filepath);
    }

    /**
     * @return array
     */
    public function getSupportedMimeTypes()
    {
        return ['image/jpeg', 'image/png', 'image/jpg'];
    }

    /**
     * @return bool
     */
    public function isRunnable()
    {
        return true;
    }
}