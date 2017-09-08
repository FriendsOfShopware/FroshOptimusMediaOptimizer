<?php

namespace TinectOptimusOptimizer\Components;

/**
 * Class OptimusService
 * @package TinectOptimusOptimizer\Components
 */
class OptimusService
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $endpoint = 'https://api.optimus.io';

    /**
     * Optimize and compress your image but keep the metadata
     * @var string
     */
    const OPTION_OPTIMIZE = 'optimize';

    /**
     * Optimize and convert your image to WebP
     * @var string
     */
    const OPTION_WEBP = 'webp';

    /**
     * @param string $apiKey
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     * @return $this
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = (string)$apiKey;
        return $this;
    }

    /**
     * @param string $image
     * @param string $option
     * @param string $target
     * @return void
     * @throws OptimusApiException
     */
    public function optimize($image, $option = self::OPTION_OPTIMIZE, $target = '')
    {

        $endpoint = $this->endpoint . '/' . $this->apiKey . '?' . $option;

        $headers = array(
            'User-Agent: Optimus-API',
            'Accept: image/*'
        );

        if ($target === '') {
            $target = $image;
        }

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $endpoint,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => file_get_contents($image),
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_SSL_VERIFYPEER => true
        ));

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($response, $header_size);

        // error catching
        if (!empty($curlError) || empty($body) || $httpcode != 200) {
            throw new OptimusApiException($body);
        }

        file_put_contents($target, $body);
    }
}