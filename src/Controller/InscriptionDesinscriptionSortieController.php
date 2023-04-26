<?php

namespace App\Controller;

use App\Entity\Sortie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @method getDoctrine()
 */
class InscriptionDesinscriptionSortieController extends AbstractController
{
    #[Route('/sortie/inscrire/{id}', name: 'inscrire')]
    public function inscrire(int $id)
    {
        $SortieRepository = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $SortieRepository->find($id);

        # inscription de l'utilisateur s'il reste de la place
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

    #[Route('/sortie/desinscrire/{id}',
        name: 'desinscrire')]

    public function desinscrire(int $id)
    {
        $SortieRepository = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $SortieRepository->find($id);

        if (!$sortie)
        {
            $this->addFlash('danger', "Cette sortie n'existe pas");
        }

        # supprimer l'utilisateur de la liste des participants
        $sortie->removeParticipant($this->getUser());

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->redirectToRoute('home');
    }
}