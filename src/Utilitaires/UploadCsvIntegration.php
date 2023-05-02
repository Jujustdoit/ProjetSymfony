<?php

namespace App\Utilitaires;

use League\Csv\Reader;
use League\Csv\Statement;

class UploadCsvIntegration
{
    public function loadCsvAction()
    {
        // Chemin vers fichier CSV
        $fichierCsv = $this->getParameter('kernel.project_dir').'/upload/participants/Inscriptions.csv';

        $reader = Reader::createFromPath($fichierCsv, 'r');

        // Instance de Statement pour utiliser des requêtes sur le fichier CSV
        $stmt = new Statement();

        // Utilisez le Statement pour exécuter une requête et récupérer les enregistrements
        $donnees = $stmt->process($reader);
        dd($donnees);

        foreach ($donnees as $donnee) {
            $email = $donnee[0]; // Valeur de la première colonne
            $role = $donnee[1];
            $motDePasse = $donnee[2];
            $nom = $donnee[3];
            $prenom = $donnee[4];
            $telephone = $donnee[5];
            $pseudo = $donnee[6];
            $admin = $donnee[7];
            $actif = 1;
        }



        return $this->render('load_csv.html.twig');
    }

}