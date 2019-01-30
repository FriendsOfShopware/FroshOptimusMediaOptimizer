<?php


class OptimusTest extends Enlight_Components_Test_Controller_TestCase
{

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    public function setUp()
    {
        parent::setUp();

        $this->connection = Shopware()->Container()->get('dbal_connection');
        $this->dispatch('/');
    }

    public function testTest()
    {
        $optimizerService = Shopware()->Container()->get('optimus_optimizer.service');

        $this->addWarning('heeeeelllooo, this should be a warning');
        $this->addWarning('date is '. $optimizerService->getValidationDate());


        $this->assertNotEmpty($optimizerService->getApiKey(), 'There is no API-Key');
        $this->assertTrue($optimizerService->verifyApiKey(), 'API-Key not valid!');

    }

    /* Add Warnings */
    protected function addWarning($msg, Exception $previous = null)
    {
        $add_warning = $this->getTestResultObject();
        $msg = new PHPUnit_Framework_Warning($msg, 0, $previous);
        $add_warning->addWarning($this, $msg, time());
        $this->setTestResultObject($add_warning);
    }
}
