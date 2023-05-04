<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Utilitaires\UploadCsvIntegration;
use App\Entity\Campus;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Route('/campus', name: 'campus_')]
//#[Security('is_granted(\'ROLE_ADMINISTRATEUR\')')]
class CampusController extends AbstractController
{

    #[Route('/index', name: 'index')]
    public function index(Request $request, CampusRepository $campusRepository, EntityManagerInterface $entityManager): Response
    {
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
                'required'=>false
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

    /* Cette fonction gère l'upload de fichier CSV et à l'issu le retrait du fichier correspondant */
    #[Route('/uploadCsv', name: 'uploadCsv')]
    public function uploadCsv(ParameterBagInterface $parameterBag, CampusRepository $campusRepository, UploadCsvIntegration $uploadCsvIntegration, UserPasswordHasherInterface $userPasswordHasher, Request $request, EntityManagerInterface $entityManager)
    {
        $nouveauxParticipants = [];

        $filesystem = new Filesystem(); // Pour effectuer des actions sur des fichiers (la suppression ici)

        // ParameterBagInterface permet de récupérer le chemin du dossier des fichiers CSV
        $dossierCsv = $parameterBag->get('kernel.project_dir') . '/public/upload/participants';

        // Finder permet de parcourir et récupérer les fichiers du dossier
        $finder = new Finder();
        $fichiers = $finder->files()->in($dossierCsv);

        $formViews = []; // Stocke les vues des formulaires
        $nomFichiers = [];

        // Itération sur les fichiers et création d'un formulaire par fichier
        foreach ($fichiers as $index => $fichier) {
            $nomFichier = $fichier->getFilename();
            $nomFichiers[] = $nomFichier; // Pour l'afficher sur la vue

            $form = $this->createFormBuilder()
                ->add('nomFichier', HiddenType::class, [
                    'data' => $nomFichier,
                ])
                ->setAction($this->generateUrl('campus_uploadCsv'))
                ->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $nomFichier = $form->get('nomFichier')->getData(); // Récupération du nom de fichier selectionné
                $uploadCsvIntegration = new UploadCsvIntegration(); // Instance de l'utilitaire
                $nouveauxParticipants = $uploadCsvIntegration->loadCsvAction($nomFichier, $dossierCsv, $campusRepository, $parameterBag, $userPasswordHasher, $entityManager);
                $filesystem->remove($dossierCsv . '/' . $nomFichier); // suppression du fichier CSV
                return $this->redirectToRoute('campus_uploadCsv');
            }

            $formViews[] = $form->createView(); // Ajoute la vue du formulaire à la liste

        }

        return $this->render('campus/upload.html.twig', [
            'fichiersCsv' => $nomFichiers,
            //'nouveauxParticipants' => $nouveauxParticipants,
            'formViews' => $formViews,
        ]);
    }

}
