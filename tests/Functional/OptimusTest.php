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

        $this->assertNotEmpty($optimizerService->getApiKey(), 'There is no API-Key');
        $this->assertTrue($optimizerService->verifyApiKey());

    }
}
