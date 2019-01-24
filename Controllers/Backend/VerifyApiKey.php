<?php


use FroshOptimusMediaOptimizer\Components\OptimusService;

class Shopware_Controllers_Backend_VerifyApiKey extends \Enlight_Controller_Action implements \Shopware\Components\CSRFWhitelistAware
{
    public function getWhitelistedCSRFActions()
    {
        return ['index'];
    }

    public function indexAction()
    {
        $this->container->get('front')->Plugins()->ViewRenderer()->setNoRender();
        $config = Shopware()->Container()->get('shopware.plugin.config_reader')->getByPluginName('FroshOptimusMediaOptimizer');
        
        if (!$config['optimusLicenseKey']) {
            echo "Key is missing! Saved?";
        } else {
            $optimus = new OptimusService($config['optimusLicenseKey']);
            if ($optimus->verifyApiKey()) {
                $this->response->setBody($config['optimusLicenseKey'] . " is valid");
            } else {
                $this->response->setBody($config['optimusLicenseKey'] . " is NOT valid");
            }
        }
    }


}

?>