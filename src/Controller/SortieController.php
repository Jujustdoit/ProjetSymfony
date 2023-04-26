<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\LieuType;
use App\Form\SortieType;
use App\Form\VilleType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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

    #[Route('/create', name: 'create')]
    //#[isGranted(['ROLE_PARTICIPANT'])]
    public function create(Request $request, EtatRepository $etatRepository, ParticipantRepository $participantRepository, EntityManagerInterface $entityManager): Response
    {
        //$organisateur = $this->getUser();
        $organisateur = $participantRepository->findOneBy(['nom'=>'Thib']);

        $sortie = new Sortie();
        $sortie->setParticipant($organisateur);
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $lieuForm->handleRequest($request);

        $ville = new Ville();
        $villeForm = $this->createForm(VilleType::class, $ville);
        $villeForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid() && $lieuForm->isSubmitted() && $lieuForm->isValid() && $villeForm->isSubmitted() && $villeForm->isValid()) {

            if ($sortieForm->get('enregistrer')) {
                $sortie->setEtat($etatRepository->findOneBy(['libelle'=>'Créée']));
            } elseif ($sortieForm->get('publier')) {
                $sortie->setEtat($etatRepository->findOneBy(['libelle'=>'Ouverte']));
            }

            $entityManager->persist($ville);
            $entityManager->flush();

            $lieu->setVille($ville);

            $entityManager->persist($lieu);
            $entityManager->flush();

            $sortie->setLieu($lieu);

            $entityManager->persist($sortie);
            $entityManager->flush();

            dd($sortie);

            if ($sortieForm->get('enregistrer')) {
                $this->addFlash('success','La sortie est créée !!');
            } elseif ($sortieForm->get('publier')) {
                $this->addFlash('success','La sortie est publiée !!');

            }
            return $this->redirectToRoute('sortie_index');
        }
        return $this->render('sortie/create.html.twig',['sortieForm' => $sortieForm->createView(),
                                                            'lieuForm'=>$lieuForm->createView(),
                                                            'villeForm'=>$villeForm->createView()]);

    }
}
