<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\Etat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @method getDoctrine()
 */
class InscriptionDesinscriptionSortieController extends AbstractController
{
    #[Route('/sortie/{id}/inscrire',
        name: 'inscrire')]

    public function inscrire(int $id)
    {
        //recherche de la sortie by $id
        $SortieRepository = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $SortieRepository->find($id);

        //TODO on check si la sortie est dans l'état open
        if ($sortie->getEtat()->get)
            // inscription de l'utilisateur s'il reste de la place
            if (count($sortie->getParticipants()) < $sortie->getNbInscriptionsMax)
            {
                $sortie->addParticipant($this->getUser());

                $em = $this->getDoctrine()->getManager();
                $em->persist($sortie);
                $em->flush();
            } else
            {
                $this->addFlash('danger', "Dommage il n'y a plus de places !");
            }

        return $this->redirectToRoute('home');
    }

    #[Route('/sortie/{id}/desinscrire',
        name: 'desinscrire')]

    public function desinscrire(int $id)
    {
        $SortieRepository = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $SortieRepository->find($id);

        if (!$sortie)
        {
            $this->addFlash('danger', "Cette sortie n'existe pas");
        }

        // supprimer l'utilisateur de la liste des participants
        $sortie->removeParticipant($this->getUser());

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->redirectToRoute('home');
    }
}