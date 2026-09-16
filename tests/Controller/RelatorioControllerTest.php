<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RelatorioControllerTest extends WebTestCase
{
    public function testRelatorioListaLivrosPorAutor(): void
    {
        $client = static::createClient();
        $client->request('GET', '/relatorio');

        $this->assertResponseIsSuccessful();
        // Agrupa por autor e inclui autoria múltipla (Design Patterns)
        $this->assertSelectorTextContains('h1', 'Relatório de livros por autor');
        $conteudo = $client->getResponse()->getContent();
        $this->assertStringContainsString('Machado de Assis', $conteudo);
        $this->assertStringContainsString('Design Patterns', $conteudo);
        $this->assertStringContainsString('vw_relatorio_livros', $conteudo);
    }
}