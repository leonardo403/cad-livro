<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AssuntoControllerTest extends WebTestCase
{
    public function testListagemDeAssuntos(): void
    {
        $client = static::createClient();
        $client->request('GET', '/assuntos');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Assuntos');
        $this->assertStringContainsString('Romance', $client->getResponse()->getContent());
    }
}