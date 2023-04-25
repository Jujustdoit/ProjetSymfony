<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/sortie', name: 'sortie_')]

class SortieController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('sortie/index.html.twig', [
            'controller_name' => 'SortieController',
        ]);
    }

    #[Route('/creation', name: 'create')]
    public function create(Request $request, EtatRepository $etatRepository, EntityManagerInterface $entityManager): Response
    {
        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            if ($sortieForm->get('enregistrer')) {
                $sortie->setEtat($etatRepository->findOneBy(['libelle'=>'Créée']));
            } elseif ($sortieForm->get('publier')) {
                $sortie->setEtat($etatRepository->findOneBy(['libelle'=>'Ouverte']));
            }


            $entityManager->persist($sortie);
            $entityManager->flush();

            if ($sortieForm->get('enregistrer')) {
                $this->addFlash('success','La sortie est créée !!');
            } elseif ($sortieForm->get('publier')) {
                $this->addFlash('success','La sortie est publiée !!');

            }
            return $this->redirectToRoute('index');
        }
        return $this->render('sortie/create.html.twig',['sortieForm' => $sortieForm->createView()]);

    }
}
