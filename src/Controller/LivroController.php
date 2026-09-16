<?php

namespace App\Controller;

use App\Entity\Livro;
use App\Form\LivroType;
use App\Repository\LivroRepository;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LivroController extends AbstractController
{
    public function __construct(
        private readonly LivroRepository $livroRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/livros', name: 'livro_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('livro/index.html.twig', [
            'livros' => $this->livroRepository->findAll(),
        ]);
    }

    #[Route('/livros/novo', name: 'livro_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $livro = new Livro();
        $form = $this->createForm(LivroType::class, $livro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->persist($livro);
                $this->entityManager->flush();

                $this->addFlash('success', 'Livro cadastrado com sucesso.');

                return $this->redirectToRoute('livro_index');
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('danger', 'Já existe um livro com esses dados.');
            } catch (DBALException $e) {
                $this->addFlash('danger', 'Não foi possível salvar o livro no banco de dados.');
            }
        }

        return $this->render('livro/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/livros/{id}/editar', name: 'livro_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Livro $livro, Request $request): Response
    {
        $form = $this->createForm(LivroType::class, $livro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->flush();

                $this->addFlash('success', 'Livro atualizado com sucesso.');

                return $this->redirectToRoute('livro_index');
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('danger', 'Já existe um livro com esses dados.');
            } catch (DBALException $e) {
                $this->addFlash('danger', 'Não foi possível atualizar o livro no banco de dados.');
            }
        }

        return $this->render('livro/edit.html.twig', [
            'livro' => $livro,
            'form' => $form,
        ]);
    }

    #[Route('/livros/{id}', name: 'livro_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Livro $livro, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete_livro_'.$livro->getCodl(), $request->request->get('_token'))) {
            try {
                $this->entityManager->remove($livro);
                $this->entityManager->flush();

                $this->addFlash('success', 'Livro removido com sucesso.');
            } catch (DBALException $e) {
                $this->addFlash('danger', 'Não foi possível remover o livro. Ele pode estar em uso.');
            }
        }

        return $this->redirectToRoute('livro_index');
    }
}