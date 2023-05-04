<?php

namespace App\Utilitaires;

use App\Entity\Participant;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Csv\Statement;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UploadCsvIntegration
{

    /* Charge un fichier CSV, lit les enregistrements et intègre chaque participant à la base de données */
    public function loadCsvAction($nomFichier, $dossier, CampusRepository $campusRepository, ParameterBagInterface $parameterBag, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager)
    {
        // Chemin vers fichier CSV
        $fichierCsv = $dossier.'/'.$nomFichier;

        // Instance de l'objet Reader pour lire le CSV ('r' : mode lecture)
        $reader = Reader::createFromPath($fichierCsv, 'r');

        // Instance de Statement (objet Reader) pour utiliser des requêtes sur le fichier CSV
        $stmt = new Statement();

        // Requête pour récupérer les enregistrements
        $donnees = $stmt->process($reader);

        $participants = [];

        foreach ($donnees as $donnee) {
            $participant = new Participant();
            $participant->setNom($donnee[3]);
            $participant->setPrenom($donnee[4]);
            $participant->setTelephone($donnee[5]);
            $participant->setEmail($donnee[0]);
            $newPassword = $userPasswordHasher->hashPassword($participant, $donnee[2]);
            $participant->setPassword($newPassword);
            $participant->setAdministrateur(0);
            $participant->setRoles([$donnee[1]]);
            $participant->setCampus($campusRepository->findOneBy(['nom'=>$donnee[7]]));
            $participant->setPseudo($donnee[6]);
            $participant->setActif(1);

            $entityManager->persist($participant);
            $entityManager->flush();

            $participants[] = $participant;
        }

        return $participants;
    }

}