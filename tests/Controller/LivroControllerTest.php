<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LivroControllerTest extends WebTestCase
{
    public function testListagemDeLivros(): void
    {
        $client = static::createClient();
        $client->request('GET', '/livros');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Livros');
        $this->assertStringContainsString('Dom Casmurro', $client->getResponse()->getContent());
        $this->assertStringContainsString('R$ 34,90', $client->getResponse()->getContent());
    }

    public function testPaginaDeNovoLivro(): void
    {
        $client = static::createClient();
        $client->request('GET', '/livros/novo');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Novo livro');
    }
}