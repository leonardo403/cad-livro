<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AutorControllerTest extends WebTestCase
{
    public function testListagemDeAutores(): void
    {
        $client = static::createClient();
        $client->request('GET', '/autores');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Autores');
        $this->assertStringContainsString('Machado de Assis', $client->getResponse()->getContent());
    }
}