<?php

class Shopware_Controllers_Backend_VerifyApiKey extends \Enlight_Controller_Action implements \Shopware\Components\CSRFWhitelistAware
{
    public function getWhitelistedCSRFActions()
    {
        return ['index'];
    }

    public function indexAction()
    {
        $this->container->get('front')->Plugins()->ViewRenderer()->setNoRender();
        $optimusService = $this->container->get('optimus_optimizer.service');

        if (!$optimusService->getApiKey()) {
            $this->response->setBody('Key is missing! Saved?');
        } else {
            if ($optimusService->verifyApiKey()) {
                $this->response->setBody($optimusService->getApiKey() . ' is valid');
            } else {
                $this->response->setBody($optimusService->getApiKey() . ' is NOT valid');
            }
        }
    }
}
