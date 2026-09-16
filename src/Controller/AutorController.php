<?php

namespace App\Controller;

use App\Entity\Autor;
use App\Form\AutorType;
use App\Repository\AutorRepository;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AutorController extends AbstractController
{
    public function __construct(
        private readonly AutorRepository $autorRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/autores', name: 'autor_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('autor/index.html.twig', [
            'autores' => $this->autorRepository->findAll(),
        ]);
    }

    #[Route('/autores/novo', name: 'autor_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $autor = new Autor();
        $form = $this->createForm(AutorType::class, $autor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->persist($autor);
                $this->entityManager->flush();

                $this->addFlash('success', 'Autor cadastrado com sucesso.');

                return $this->redirectToRoute('autor_index');
            } catch (DBALException $e) {
                $this->addFlash('danger', 'Não foi possível salvar o autor no banco de dados.');
            }
        }

        return $this->render('autor/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/autores/{id}/editar', name: 'autor_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Autor $autor, Request $request): Response
    {
        $form = $this->createForm(AutorType::class, $autor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->flush();

                $this->addFlash('success', 'Autor atualizado com sucesso.');

                return $this->redirectToRoute('autor_index');
            } catch (DBALException $e) {
                $this->addFlash('danger', 'Não foi possível atualizar o autor no banco de dados.');
            }
        }

        return $this->render('autor/edit.html.twig', [
            'autor' => $autor,
            'form' => $form,
        ]);
    }

    #[Route('/autores/{id}', name: 'autor_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Autor $autor, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete_autor_'.$autor->getCodAu(), $request->request->get('_token'))) {
            try {
                $this->entityManager->remove($autor);
                $this->entityManager->flush();

                $this->addFlash('success', 'Autor removido com sucesso.');
            } catch (DBALException $e) {
                $this->addFlash('danger', 'Não foi possível remover o autor. Ele pode estar em uso.');
            }
        }

        return $this->redirectToRoute('autor_index');
    }
}