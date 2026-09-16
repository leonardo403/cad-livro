<?php

namespace App\Controller;

use App\Entity\Assunto;
use App\Form\AssuntoType;
use App\Repository\AssuntoRepository;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AssuntoController extends AbstractController
{
    public function __construct(
        private readonly AssuntoRepository $assuntoRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/assuntos', name: 'assunto_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('assunto/index.html.twig', [
            'assuntos' => $this->assuntoRepository->findAll(),
        ]);
    }

    #[Route('/assuntos/novo', name: 'assunto_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $assunto = new Assunto();
        $form = $this->createForm(AssuntoType::class, $assunto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->persist($assunto);
                $this->entityManager->flush();

                $this->addFlash('success', 'Assunto cadastrado com sucesso.');

                return $this->redirectToRoute('assunto_index');
            } catch (DBALException $e) {
                $this->addFlash('danger', 'Não foi possível salvar o assunto no banco de dados.');
            }
        }

        return $this->render('assunto/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/assuntos/{id}/editar', name: 'assunto_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Assunto $assunto, Request $request): Response
    {
        $form = $this->createForm(AssuntoType::class, $assunto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->flush();

                $this->addFlash('success', 'Assunto atualizado com sucesso.');

                return $this->redirectToRoute('assunto_index');
            } catch (DBALException $e) {
                $this->addFlash('danger', 'Não foi possível atualizar o assunto no banco de dados.');
            }
        }

        return $this->render('assunto/edit.html.twig', [
            'assunto' => $assunto,
            'form' => $form,
        ]);
    }

    #[Route('/assuntos/{id}', name: 'assunto_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Assunto $assunto, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete_assunto_'.$assunto->getCodAs(), $request->request->get('_token'))) {
            try {
                $this->entityManager->remove($assunto);
                $this->entityManager->flush();

                $this->addFlash('success', 'Assunto removido com sucesso.');
            } catch (DBALException $e) {
                $this->addFlash('danger', 'Não foi possível remover o assunto. Ele pode estar em uso.');
            }
        }

        return $this->redirectToRoute('assunto_index');
    }
}