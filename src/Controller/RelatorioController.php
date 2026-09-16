<?php

namespace App\Controller;

use App\Repository\LivroRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RelatorioController extends AbstractController
{
    #[Route('/relatorio', name: 'relatorio_index', methods: ['GET'])]
    public function index(LivroRepository $livroRepository): Response
    {
        // Dados vêm da view vw_relatorio_livros, já ordenados por autor/título.
        $linhas = $livroRepository->consultarRelatorio();

        // Agrupa os livros por autor para exibição no relatório.
        $porAutor = [];
        foreach ($linhas as $linha) {
            $porAutor[$linha['nome_autor']][] = $linha;
        }

        return $this->render('relatorio/index.html.twig', [
            'porAutor' => $porAutor,
            'totalLivros' => count($linhas),
        ]);
    }
}