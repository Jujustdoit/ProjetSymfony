<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\FiltreType;
use App\Form\LieuType;
use App\Form\SortieType;
use App\Form\VilleType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
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
        //$user = $this->getUser();
        $user = $participantRepository->findOneBy(['nom'=>'Letellier']);

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
        //$organisateur = $this->getUser();
        $organisateur = $participantRepository->findOneBy(['nom'=>'Letellier']);

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


}
