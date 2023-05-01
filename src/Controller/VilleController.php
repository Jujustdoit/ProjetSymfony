<?php

namespace App\Controller;

use App\Repository\VilleRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ville', name: 'ville_')]
//#[Security('is_granted(\'ROLE_ADMINISTRATEUR\')')]
class VilleController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, VilleRepository $villeRepository): Response
    {

        $searchVilleForm = $this->createFormBuilder()
            ->add('nomVille', TextType::class,['label' => 'Le nom contient : ','required'=> false])
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

        return $this->render('ville/index.html.twig', [
            'searchVilleForm'=>$searchVilleForm->createView(),
            'villes'=>$villes
        ]);
    }

    #[Route('/', name: 'update')]
    public function update($id) {

    }

    #[Route('/', name: 'delete')]
    public function delete($id) {

    }
}
