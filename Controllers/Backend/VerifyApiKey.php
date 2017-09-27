<?php


use TinectOptimusOptimizer\Components\OptimusService;

class Shopware_Controllers_Backend_VerifyApiKey extends \Enlight_Controller_Action implements \Shopware\Components\CSRFWhitelistAware
{
    public function getWhitelistedCSRFActions()
    {
        return ['index'];
    }

    public function indexAction()
    {

        $this->container->get('front')->Plugins()->ViewRenderer()->setNoRender();
        $config = Shopware()->Container()->get('shopware.plugin.config_reader')->getByPluginName('TinectOptimusOptimizer');

        $optimus = new OptimusService($config['optimusLicenseKey']);
        if ($optimus->verifyApiKey()) {
            echo $config['optimusLicenseKey']. " is valid";
            exit();
        }
    }


}

?>