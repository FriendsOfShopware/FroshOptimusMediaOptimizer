<?php

namespace FroshOptimusMediaOptimizer\Commands;

use FroshOptimusMediaOptimizer\Components\MultiCurl;
use FroshOptimusMediaOptimizer\Components\OptimusOptimizer;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BatchOptimizerCommand
 */
class BatchOptimizerCommand extends ShopwareCommand
{
    const IMAGESPERHANDLE = 10;

    /**
     * @var int
     */
    private $imagesInHandle = 0;

    /**
     * @var MultiCurl
     */
    private $curlHandle = null;

    /**
     * @var array
     */
    private $pluginConfig;

    /**
     * @param $imageResult
     * @param $url
     * @param array $options
     */
    public static function processCurlCallback($imageResult, $url, array $options)
    {
        $item = $options['user_data']['item'];
        /** @var MediaServiceInterface $mediaService */
        $mediaService = $options['user_data']['mediaService'];
        /** @var ProgressBar $progressBar */
        $progressBar = $options['user_data']['progressBar'];

        if ($options['http_code'] === 200) {
            $mediaService->write($item['path'], $imageResult);
        }

        $progressBar->advance();
    }

    protected function configure()
    {
        $this->setName('optimus:batch');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mediaService = $this->getContainer()->get('shopware_media.media_service');
        $numberOfFiles = count(array_filter($mediaService->getFilesystem()->listContents('media', true), function (array $element) {
            return $element['type'] === 'file';
        }));

        $progress = new ProgressBar($output, $numberOfFiles);
        $this->curlHandle = new MultiCurl(self::IMAGESPERHANDLE);
        $this->pluginConfig = $this->container->get('optimus_optimizer.config');

        $this->optimizeFiles('media', $mediaService, $progress, $output);

        $progress->finish();
    }

    /**
     * @param $directory
     * @param MediaServiceInterface $mediaService
     * @param ProgressBar           $progressBar
     * @param OutputInterface       $output
     */
    private function optimizeFiles(
        $directory,
        MediaServiceInterface $mediaService,
        ProgressBar $progressBar,
        OutputInterface $output)
    {
        /** @var array $contents */
        $contents = $mediaService->getFilesystem()->listContents($directory);

        foreach ($contents as $item) {
            if ($item['type'] === 'dir') {
                $this->optimizeFiles($item['path'], $mediaService, $progressBar, $output);
                continue;
            }

            if ($item['type'] === 'file') {
                if (strpos($item['basename'], '.') === 0) {
                    $progressBar->advance();
                    continue;
                }

                if (!in_array($this->getMimeTypeByFile($item['path']), OptimusOptimizer::$supportedMimeTypes) && $this->getMimeTypeByFile($item['path']) != 'image/webp') {
                    $progressBar->advance();
                    continue;
                }

                $this->curlHandle->addRequest(
                    'https://api.optimus.io/' . $this->pluginConfig['optimusLicenseKey'] . '?optimize',
                    $mediaService->getFilesystem()->read($item['path']),
                    [$this, 'processCurlCallback'],
                    ['item' => $item, 'mediaService' => $mediaService, 'progressBar' => $progressBar],
                    null,
                    [
                        'User-Agent: Optimus-API',
                        'Accept: image/*',
                    ]
                );

                ++$this->imagesInHandle;

                if ($this->imagesInHandle === self::IMAGESPERHANDLE) {
                    $this->curlHandle->execute();
                    $this->curlHandle->reset();
                    $this->imagesInHandle = 0;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    private function getMimeTypeByFile($filepath)
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        return $finfo->file($filepath);
    }
}
