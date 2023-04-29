<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class AppFixtures extends Fixture

{
    private UserPasswordHasherInterface $hasher;
    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        //fixtures des campus
        $campusNom = ['CAMPUS DE RENNES', 'CAMPUS DE NANTES', 'CAMPUS EN LIGNE'];
        $campuses = [];
        foreach ($campusNom as $nom) {
            $campus = new Campus();
            $campus->setNom($nom);
            $manager->persist($campus);
            $campuses[] = $campus;
        }

        //fixtures des états (imcomplet juste pour tester)
        $etatLibelle = ['Créée', 'Ouverte', 'Clôturée', 'En cours', 'Passée', 'Annulée'];
        $etats = [];
        foreach ($etatLibelle as $libelle) {
            $etat = new Etat();
            $etat->setLibelle($libelle);
            $manager->persist($etat);
            $etats[] = $etat;
        }

        //utilisation du faker en français @valeur = 1201
        $faker = Factory::create('fr_FR');
        $faker->seed(1201);

        //création de 10 participants
        $participants = array();
        for ($i = 0; $i < 10; $i++) {
            $participants[$i] = new Participant();
            $participants[$i]->setEmail("participant$i@participant.com");
            $participants[$i]->setNom($faker->lastName);
            $participants[$i]->setPrenom($faker->firstName);
            $participants[$i]->setPseudo($faker->userName);
            $participants[$i]->setAdministrateur(true);
            $participants[$i]->setActif(true);
            $participants[$i]->setTelephone($faker->phoneNumber);
            $participants[$i]->setRoles(['ROLE_ORGANISATEUR']);

            $password = $this->hasher->hashPassword($participants[$i], 'pass_1201');
            $participants[$i]->setPassword($password);

            $participants[$i]->setCampus($campuses[mt_rand(0, 2)]);
            $manager->persist($participants[$i]);
        }

        //création des villes
        $villes = [];
        for ($i = 0; $i < 10; $i++) {
            $ville = new Ville();
            $ville->setNom($faker->word);
            $ville->setCodePostal((int)($faker->postcode));
            $manager->persist($ville);
            $villes[] = $ville;
        }

        //création des lieux
        $lieux = array();
        for ($i = 0; $i < 15; $i++) {
            $lieux[$i] = new Lieu();
            $lieux[$i]->setNom($faker->word);
            $lieux[$i]->setRue($faker->streetAddress);
            $lieux[$i]->setVille($villes[mt_rand(0, 9)]);
            $lieux[$i]->setLatitude($faker->latitude);
            $lieux[$i]->setLongitude(($faker->longitude));
            $manager->persist($lieux[$i]);
        }

        //création des sorties

        $towns = [
            'Champigneulles', 'Nancy', 'Villiers-Sur-Marne', 'Paris', 'Nîmes',
            'Lorient', 'Lyon', 'Tour', 'Poitiers', 'Villeurbane',
            'Marseille', 'Rouen', 'Nantes', 'Limoges', 'Dijon',
            'Valence', 'Bayonne', 'Brest', 'Dunkerque', 'Montpellier'
        ];

        $sortieNom = ['Sortie à ', 'Allons à ', 'Visite de ', 'Fête à ', 'Let\'s go to '];
        $sortieDescription = ['Partons tous à ', 'Allons nous amuser à ', 'Rendez-vous à l\'école pour aller à ', 'Fête à '];
        for ($i = 0; $i < count($towns); $i++) {
            $sortie = new Sortie();
            $sortie->setNom($sortieNom[mt_rand(0, count($sortieNom) - 1)] . $towns[$i]);
            $sortie->setInfosSortie($sortieDescription[mt_rand(0, count($sortieDescription) - 1)] . $towns[$i]);
            $sortie->setCampus($campuses[mt_rand(0, 2)]);
            $sortie->setOrganisateur($participants[mt_rand(0, 9)]);
            //$minutes = strval(mt_rand(5, 120)) . ' minutes';
            $sortie->setDuree($faker->randomNumber(3, false));
            $sortie->setNbInscriptionsMax(mt_rand(2, 25));
            $date1 = $faker->dateTimeBetween('now', '+40 days');
            $date2 = $faker->dateTimeBetween('-40 days', 'now');

//            $dateString = date_format($date1, 'd-m-Y');
//            $date2 = $faker->dateTimeBetween($dateString, '+7 days');
            if ($date1 < $date2)
            {
                $sortie->setDateHeureDebut($date1);
                $sortie->setDateLimiteInscription($date2);
            } else
            {
                $sortie->setDateHeureDebut($date2);
                $sortie->setDateLimiteInscription($date1);
            }
            $sortie->setLieu($lieux[mt_rand(0, 14)]);
            $sortie->setEtat($etats[mt_rand(0, 1)]);
            $manager->persist($sortie);
        }
        $manager->flush();
    }
}
