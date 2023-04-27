<?php

namespace App\Controller;

use App\Entity\Sortie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @method getDoctrine()
 */
class InscriptionDesinscriptionSortieController extends AbstractController
{
    #[Route('/sortie/inscrire/{idSortie}/{idUser}',
        name: 'inscrire')]

    public function inscrire(int $id)
    {
        //recherche de la sortie by $id
        $SortieRepository = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $SortieRepository->find($id);

        //TODO vérifier la condition état de la sortie
        //on vérifie que la sortie soit ouverte
        if ($sortie->getEtat()->getNom() !== "Ouvert")
        {
            $this->addFlash("danger", "Cette sortie n'est pas ouverte aux inscriptions !");
            return $this->redirectToRoute('/sorties/details/{id}', ["id" => $sortie->getId()]);
        }

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

        $this->addFlash("success", "Vous êtes désinscrit !");
        return $this->redirectToRoute('home');
    }
}