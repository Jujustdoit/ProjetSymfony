<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ville', name: 'ville_')]
//#[Security('is_granted(\'ROLE_PARTICIPANT\')')]
class VilleController extends AbstractController
{
    #[Route('/index', name: 'index')]
    public function index(Request $request, VilleRepository $villeRepository, EntityManagerInterface $entityManager): Response
    {

        $searchVilleForm = $this->createFormBuilder()
            ->add('nomVille', TextType::class,[
                'label' => 'Le nom contient : ',
                'required'=> false
            ])
            ->getForm();

        $searchVilleForm->handleRequest($request);

        if ($searchVilleForm->isSubmitted() && $searchVilleForm->isValid()) {
            $donnees = $searchVilleForm->getData();
            $villes = $villeRepository->filtrer(
                $donnees['nomVille']
            );
        } else {
            $villes = $villeRepository->findAll();
        }

        $villeForm = $this->createFormBuilder()
            ->add('nom', TextType::class, ['required'=>false])
            ->add('codePostal', TextType::class, ['required'=>false])
            ->getForm();

        $villeForm->handleRequest($request);

        if ($villeForm->isSubmitted() && $villeForm->isValid()) {
            $villeAjout = new Ville();
            $villeAjout->setNom($villeForm->get('nom')->getData());
            $villeAjout->setCodePostal($villeForm->get('codePostal')->getData());

            $entityManager->persist($villeAjout);
            $entityManager->flush();

            return $this->redirectToRoute('ville_index');
        }

        return $this->render('ville/index.html.twig', [
            'searchVilleForm'=>$searchVilleForm->createView(),
            'villes'=>$villes,
            'villeForm'=>$villeForm->createView()
        ]);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete($id, VilleRepository $villeRepository, EntityManagerInterface $entityManager) {

        $ville = $villeRepository->find($id);

        $entityManager->remove($ville);
        $entityManager->flush();

        return $this->redirectToRoute('ville_index');
    }
}
