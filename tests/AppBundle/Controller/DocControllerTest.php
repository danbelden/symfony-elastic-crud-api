<?php

namespace Tests\AppBundle\Controller;

use DOMDocument;
use DOMXPath;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DocControllerTest extends WebTestCase
{
    const TEMPLATE_PATH = __DIR__ . '/../../../src/AppBundle/Resources/views/doc.html.twig';

    /**
     * @var Container
     */
    private $container;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var DOMXPath
     */
    private $templateXpath;

    public function setup()
    {
        $this->client = static::createClient();

        $this->container = static::$kernel->getContainer();

        $templateDoc = new DOMDocument();
        $templateDoc->loadHTMLFile(self::TEMPLATE_PATH);

        $this->templateXpath = new DOMXPath($templateDoc);
    }

    public function testDocEndpoint()
    {
        $router = $this->container->get('router');
        $url = $router->generate('api_doc');

        $crawler = $this->client->request('GET', $url);

        $response = $this->client->getResponse();
        $responseCode = $response->getStatusCode();
        $this->assertSame(200, $responseCode);

        $templatePageTitle = $this->templateXpath->evaluate('//title')->item(0)->nodeValue;
        $pageTitle = $crawler->filterXPath('//title')->text();
        $this->assertSame($templatePageTitle, $pageTitle);

        $numTemplateStylesheets = $this->templateXpath->evaluate('//link')->length;
        $numStylesheets = $crawler->filterXPath('//link')->count();
        $this->assertSame($numTemplateStylesheets, $numStylesheets);

        $numTemplateScripts = $this->templateXpath->evaluate('//script')->length;
        $numScripts = $crawler->filterXPath('//script')->count();
        $this->assertSame($numTemplateScripts, $numScripts);

        $templateAnchorText = $this->templateXpath->evaluate('//a[@id="logo"]')->item(0)->nodeValue;
        $logoAnchorText = $crawler->filterXPath('//a[@id="logo"]')->text();
        $this->assertSame($templateAnchorText, $logoAnchorText);
    }
}
