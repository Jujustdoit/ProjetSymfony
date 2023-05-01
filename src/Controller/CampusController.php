<?php

namespace App\Controller;

use App\Repository\CampusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/campus', name: 'campus_')]
class CampusController extends AbstractController
{

    #[Route('/', name: 'index')]
    public function index(Request $request, CampusRepository $campusRepository): Response
    {

        $searchCampusForm = $this->createFormBuilder()
            ->add('nomCampus', TextType::class,['label' => 'Le nom contient : ','required'=> false])
            ->getForm();

        $searchCampusForm->handleRequest($request);

        if ($searchCampusForm->isSubmitted() && $searchCampusForm->isValid()) {
            $donnees = $searchCampusForm->getData();
            $campus = $campusRepository->filtrer(
                $donnees['nomCampus']
            );
        } else {
            $campus = $campusRepository->findAll();
        }

        return $this->render('campus/index.html.twig', [
            'searchCampusForm'=>$searchCampusForm->createView(),
            'campus'=>$campus
        ]);
    }

    #[Route('/', name: 'update')]
    public function update($id) {

    }

    #[Route('/', name: 'delete')]
    public function delete($id) {

    }
}
