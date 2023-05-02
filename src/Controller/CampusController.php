<?php

namespace App\Controller;

use App\Utilitaires\UploadCsvIntegration;
use App\Entity\Campus;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Route('/campus', name: 'campus_')]
//#[Security('is_granted(\'ROLE_ADMINISTRATEUR\')')]
class CampusController extends AbstractController
{

    #[Route('/index', name: 'index')]
    public function index(UploadCsvIntegration $uploadCsvIntegration, Request $request, CampusRepository $campusRepository, EntityManagerInterface $entityManager): Response
    {
        $uploadCsvIntegration->loadCsvAction();

        $searchCampusForm = $this->createFormBuilder()
            ->add('nomCampus', TextType::class,[
                'label' => 'Le nom contient : ',
                'required'=> false,
                'constraints'=>[
                    new NotBlank([
                        'message' => 'Veuillez saisir une partie du nom du Campus'
                ])
            ]])
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

        $campusForm = $this->createFormBuilder()
            ->add('nom', TextType::class, [
                'required'=>false,
                'constraints'=>[
                    new NotBlank([
                        'message' => 'Veuillez saisir le nom du Campus'
                    ])
                ]
            ])
            ->getForm();

        $campusForm->handleRequest($request);

        if ($campusForm->isSubmitted() && $campusForm->isValid()) {
            $campusAjout = new Campus();
            $campusAjout->setNom($campusForm->get('nom')->getData());

            $entityManager->persist($campusAjout);
            $entityManager->flush();

            return $this->redirectToRoute('campus_index');
        }

        return $this->render('campus/index.html.twig', [
            'searchCampusForm'=>$searchCampusForm->createView(),
            'campus'=>$campus,
            'campusForm'=>$campusForm->createView()
        ]);
    }

    #[Route('/update', name: 'update')]
    public function update($id) {

    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete($id, CampusRepository $campusRepository, EntityManagerInterface $entityManager) {

        $campus = $campusRepository->find($id);

        $entityManager->remove($campus);
        $entityManager->flush();

        return $this->redirectToRoute('campus_index');
    }
}
