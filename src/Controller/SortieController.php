<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\LieuType;
use App\Form\SortieType;
use App\Form\VilleType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/sortie', name: 'sortie_')]

class SortieController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function home(Request $request, ParticipantRepository $participantRepository, SortieRepository $sortieRepository): Response
    {
        $user = $this->getUser();
        //$user = $participantRepository->findOneBy(['nom'=>'Letellier']);

        $criteresForm = $this->createFormBuilder()
            ->add('campus', EntityType::class, [
                'label'=>'Campus',
                'class' => Campus::class,
                'choice_label' => 'nom',
                'placeholder' => 'Tous les campus',
                'required' => false
            ])
            ->add('nomSortie', TextType::class,['label' => 'Le nom de la sortie contient : ','required'=> false])
            ->add('dateMin',DateTimeType::class, array('widget' => 'single_text','label' => 'Entre ','required'=> false))
            ->add('dateMax',DateTimeType::class, array('widget' => 'single_text','label' => 'et ','required'=> false))
            ->add('organisateur', CheckboxType::class, ['label' => 'Sorties dont je suis l\'organisateur/trice','required' => false])
            ->add('inscrit', CheckboxType::class, ['label' => 'Sorties auxquelles je suis inscrit/e','required' => false])
            ->add('pasInscrit', CheckboxType::class, ['label' => 'Sorties auxquelles je ne suis pas inscrit/e','required' => false])
            ->add('sortiesPassees', CheckboxType::class, ['label' => 'Sorties passées','required' => false])
            ->add('recherche', SubmitType::class, ['label' => 'Rechercher'])
            ->getForm()
        ;

        $criteresForm->handleRequest($request);

        if ($criteresForm->isSubmitted() && $criteresForm->isValid()) {
            $donnees = $criteresForm->getData();
            $sorties = $sortieRepository->filtrer(
                $donnees['campus'],
                $donnees['nomSortie'],
                $donnees['dateMin'],
                $donnees['dateMax'],
                $donnees['organisateur'],
                $donnees['inscrit'],
                $donnees['pasInscrit'],
                $donnees['sortiesPassees'],
                $user->getId()
                                        );
        } else {
            $sorties = $sortieRepository->findAll();
        }

        return $this->render('sortie/home.html.twig',
                                ['sorties' => $sorties,
                                'participant' => $user,
                                'criteres'=>$criteresForm->createView()
                                ]);
    }

    #[Route('/create', name: 'create')]
    //#[isGranted(['ROLE_PARTICIPANT'])]
    public function create(Request $request, EtatRepository $etatRepository, ParticipantRepository $participantRepository, EntityManagerInterface $entityManager): Response
    {
        $organisateur = $this->getUser();
        //$organisateur = $participantRepository->findOneBy(['nom'=>'Letellier']);

        $sortie = new Sortie();
        $sortie->setOrganisateur($organisateur);
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $lieuForm->handleRequest($request);

        $ville = new Ville();
        $villeForm = $this->createForm(VilleType::class, $ville);
        $villeForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid() && $lieuForm->isSubmitted() && $lieuForm->isValid() && $villeForm->isSubmitted() && $villeForm->isValid()) {
            if ($organisateur->getRoles() == ["ROLE_PARTICIPANT"]) $organisateur->setRoles(["ROLE_ORGANISATEUR"]);

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

            if ($sortieForm->get('enregistrer')) {
                $this->addFlash('success','La sortie est créée !!');
            } elseif ($sortieForm->get('publier')) {
                $this->addFlash('success','La sortie est publiée !!');
            }
            return $this->redirectToRoute('sortie_home');
        }
        return $this->render('sortie/create.html.twig',['sortieForm' => $sortieForm->createView(),
                                                            'lieuForm'=>$lieuForm->createView(),
                                                            'villeForm'=>$villeForm->createView()]);

    }

    #[Route('/update/{id}', name: 'update')]
    //#[isGranted(['ROLE_ORGANISATEUR'])]
    public function update($id,Request $request, SortieRepository $sortieRepository, LieuRepository $lieuRepository, VilleRepository $villeRepository, EtatRepository $etatRepository, EntityManagerInterface $entityManager): Response
    {

        $sortie = $sortieRepository->find($id);
        if (!$sortie) {
            throw $this->createNotFoundException("Sortie inexistante");
        }
        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        $lieu = $lieuRepository->find($sortie->getLieu()->getId());
        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $lieuForm->handleRequest($request);

        $ville = $villeRepository->find($lieu->getVille()->getId());
        $villeForm = $this->createForm(VilleType::class, $ville);
        $villeForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid() && $lieuForm->isSubmitted() && $lieuForm->isValid() && $villeForm->isSubmitted() && $villeForm->isValid()) {

            $lieu->setVille($ville);
            $sortie->setLieu($lieu);

            if ($sortieForm->get('supprimer')->isClicked()) {
                $entityManager->remove($sortie);
                $entityManager->flush();
                $this->addFlash('success','La sortie est supprimée !!');
            } else {
                if ($sortieForm->get('enregistrer')->isClicked()){
                    $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Créér']));
                } else {
                    $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Ouverte']));
                }

                $entityManager->persist($ville);
                $entityManager->flush();

                $entityManager->persist($lieu);
                $entityManager->flush();

                $entityManager->persist($sortie);
                $entityManager->flush();

                if ($sortieForm->get('enregistrer')) {
                    $this->addFlash('success','La modification est effectuée !!');
                } elseif ($sortieForm->get('publier')) {
                    $this->addFlash('success','La sortie est publiée !!');
                }
            }

            return $this->redirectToRoute('sortie_home');
        }

        return $this->render('sortie/update.html.twig',['sortieForm' => $sortieForm->createView(),
                                                            'lieuForm'=>$lieuForm->createView(),
                                                            'villeForm'=>$villeForm->createView()]);
    }

    #[Route('/inscription/{idSortie}/{idUser}', name: 'inscription')]
    //*************Création des enregistrements de la sortie avec ID de la sortie et les ID Participants****************
    public function register(
        int                    $idSortie,
        int                    $idUser,
        ParticipantRepository  $participantRepository,
        SortieRepository       $sortieRepository,
        EntityManagerInterface $entityManager,
        Request                $request
    )
    {
        //Recherche de la sortie via son ID
        $sortie = $sortieRepository->find($idSortie);
        //Recherche du participant via son ID
        $participant = $participantRepository->find($idUser);

        if ($sortie->getNbInscriptionsMax() > count($sortie->getParticipants()) && $sortie->getDateLimiteInscription() > new DateTime('NOW')) {
            $sortie->addParticipant($participant);
            //Enregistrement du participant sur la sortie
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Inscription réussie !');
            return $this->redirectToRoute('sortie_home');
        } else {
            $this->addFlash('fail', 'L\'inscription a échoué car le nombre de place est déjà rempli ou la date de clôture est dépassée !');
            return $this->redirectToRoute('sortie_home');
        }
    }

    #[Route('/desinscription/{idSortie}/{idUser}', name: 'desinscription')]
    //*********************Désinscription d'une sortie avec ID sortie et ID participant ********************************
    public function unsubscribe(
        int                    $idSortie,
        int                    $idUser,
        ParticipantRepository  $participantRepository,
        SortieRepository       $sortiesRepository,
        EntityManagerInterface $entityManager,
        Request                $request
    )
    {
        //Recherche de la sortie via son ID
        $sortie = $sortiesRepository->find($idSortie);
        //Recherche du participant via son ID
        $participant = $participantRepository->find($idUser);

        if ($sortie->getDateLimiteInscription() > new DateTime('NOW')) {
            $sortie->removeParticipant($participant);
            //Suppression du participant sur la sortie
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Désinscription réussie !');
            return $this->redirectToRoute('main_home');
        } else {
            $this->addFlash('fail', 'Vous ne pouvez vous désincrire après la fin des inscriptions !');
            return $this->redirectToRoute('main_home');
        }
    }
}
