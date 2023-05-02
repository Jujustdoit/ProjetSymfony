<?php

namespace App\EventState;

use App\Entity\Sortie;
use App\Entity\Etat;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Ce service aide à gérer les états des sorties
 *
 * Class EventStateHelper
 * @package App\EventHelper
 */
class EventStateHelper
{
    private $doctrine;

    /**
     *
     * EventStateHelper constructor.
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Retourne un objet Etat en fonction de son libelle
     *
     * @param string $libelle
     * @return Etat|object|null
     */
    public function getEtatByLibelle(string $libelle)
    {
        $etatRepo = $this->doctrine->getRepository(Etat::class);
        $etat = $etatRepo->findOneBy(['libelle' => $libelle]);

        return $etat;
    }

    /**
     * Change l'état d'un événement en bdd
     *
     * @param Sortie $sortie
     * @param string $nouveauLibelle
     */
    public function changeEventState(Etat $sortie, string $nouveauLibelle)
    {
        $nouvelEtat = $this->getEtatByLibelle($nouveauLibelle);
        $sortie->setEtat($nouvelEtat);

        $em = $this->doctrine->getManager();
        $em->persist($sortie);
        $em->flush();
    }

    /**
     *
     * Retourne un booléen en fonction de si la sortie devrait être archivée
     *
     * @param Sortie $sortie
     * @return bool
     */
    public function shouldChangeStateToArchived(Sortie $sortie): bool
    {
        $oneMonthAgo = new \DateTime("-1 month");
        if (
            $sortie->getEndDate() < $oneMonthAgo
            && $sortie->getEtat()->getLibelle() !== "archivée"
        ){
            return true;
        }

        return false;
    }

    /**
     *
     * Retourne un booléen en fonction de si la sortie devrait être classée comme "en cours"
     *
     * @param Sortie $sortie
     * @return bool
     */
    public function shouldChangeStateToOngoing(Sortie $sortie): bool
    {
        $now = new \DateTime();
        if (
            $sortie->getEtat()->getLibelle() === "fermée" &&
            $sortie->getDateHeureDebut() < $now
            && $sortie->getDateLimiteInscription() > $now
            && $sortie->getEtat()->getLibelle() !== "en cours"
        ){
            return true;
        }

        return false;
    }

    /**
     *
     * Retourne un booléen en fonction de si la sortie devrait être classée comme "terminée"
     *
     * @param Sortie $sortie
     * @return bool
     */
    public function shouldChangeStateToEnded(Sortie $sortie): bool
    {
        $now = new \DateTime();
        $oneMonthAgo = new \DateTime("-1 month");
        if (
            $sortie->getEtat()->getLibelle() === "en cours" &&
            $sortie->getEndDate() >= $oneMonthAgo &&
            $sortie->getEndDate() <= $now
            && $sortie->getEtat()->getLibelle() !== "terminée"
        ){
            return true;
        }

        return false;
    }

    /**
     *
     * Retourne un booléen en fonction de si la sortie devrait être classée comme "fermée"
     *
     * @param Sortie $sortie
     * @return bool
     */
    public function shouldChangeStateToClosed(Sortie $sortie): bool
    {
        $now = new \DateTime();

        if (
            $sortie->getEtat()->getLibelle() === "ouverte" &&
            $sortie->getDateLimiteInscription() <= $now
            && $sortie->getDateHeureDebut() > $now
            && $sortie->getEtat()->getLibelle() !== "fermée"
        ){
            echo $sortie->getDateLimiteInscription()->format("Y-m-d H:i") . " <= " . $now->format("Y-m-d H:i") . "\r\n";
            echo "closing";
            return true;
        }

        return false;
    }

    /**
     *
     * Retourne true si la sortie peut être publiée
     *
     * @param Sortie $sortie
     * @return bool
     */
    public function canBePublished(Sortie $sortie): bool
    {
        //doit être en statut "created" pour retourner true
        return $sortie->getEtat()->getLibelle() === "créée";
    }

    /**
     *
     * Retourne true si la sortie peut être annulée
     *
     * @param Sortie $sortie
     * @return bool
     */
    public function canBeCanceled(Sortie $sortie): bool
    {
        //doit être en statut "open" ou "closed" pour retourner true
        return $sortie->getEtat()->getLibelle() === "ouverte" || $sortie->getEtat()->getLibelle() === "fermée";
    }

    /**
     * devine l'état d'un event, utile pour les fixtures
     */
    public function guessEventState(Sortie $sortie): string
    {
        $now = new \DateTime();
        $oneMonthAgo = new \DateTime("-1 month");

        if ($sortie->getEndDate() < $oneMonthAgo){
            return "archivée";
        }

        if ($sortie->getEndDate() >= $oneMonthAgo && $sortie->getEndDate() <= $now){
            return "terminée";
        }

        if ($sortie->getDateHeureDebut() < $now && $sortie->getEndDate() > $now){
            return "en cours";
        }

        if ($sortie->getDateLimiteInscription() <= $now && $sortie->getDateHeureDebut() > $now){
            return "fermée";
        }

        if ($sortie->getDateHeureDebut() > $now && $sortie->getDateLimiteInscription() > $now){
            return "ouverte";
        }

        return "créée";
    }
}