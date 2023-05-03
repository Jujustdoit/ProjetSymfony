<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/lieu', name: 'lieu_')]
//#[Security('is_granted(\'ROLE_PARTICIPANT\')')]
class LieuController extends AbstractController
{
    #[Route('/index', name: 'index')]
    public function index(Request $request, LieuRepository $lieuRepository, EntityManagerInterface $entityManager): Response
    {

        $searchLieuForm = $this->createFormBuilder()
            ->add('nomLieu', TextType::class,[
                'label' => 'Le nom du lieu contient : ',
                'required'=> false
            ])
            ->getForm();

        $searchLieuForm->handleRequest($request);

        if ($searchLieuForm->isSubmitted() && $searchLieuForm->isValid()) {
            $donnees = $searchLieuForm->getData();
            $lieux = $lieuRepository->filtrer(
                $donnees['nomLieu']
            );
        } else {
            $lieux = $lieuRepository->findAll();
        }

        $nouveauLieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $nouveauLieu);
        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            $lieuAjout = new Lieu();
            $lieuAjout->setNom($lieuForm->get('nom')->getData());
            $lieuAjout->setRue($lieuForm->get('rue')->getData());
            $lieuAjout->setLatitude($lieuForm->get('latitude')->getData());
            $lieuAjout->setLongitude($lieuForm->get('longitude')->getData());
            $lieuAjout->setVille($lieuForm->get('ville')->getData());

            $entityManager->persist($lieuAjout);
            $entityManager->flush();

            return $this->redirectToRoute('lieu_index');
        }

        return $this->render('lieu/index.html.twig', [
            'searchLieuForm'=>$searchLieuForm->createView(),
            'lieux'=>$lieux,
            'lieuForm'=>$lieuForm->createView()
        ]);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete($id, lieuRepository $lieuRepository, EntityManagerInterface $entityManager) {

        $lieu = $lieuRepository->find($id);

        $entityManager->remove($lieu);
        $entityManager->flush();

        return $this->redirectToRoute('lieu_index');
    }
}
