<?php

namespace App\Command;

use App\Entity\Sortie;
use App\EventState\EventStateHelper;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande à exécuter à chaque minute pour l'éternité, avec un cron job à créer sur le serveur de prod
 * Cette commande permet de tenir à jour les états des sorties
 * Class UpdateEventStatesCommand
 * @package App\Command
 */
class UpdateEventStatesCommand extends Command
{
    protected static $defaultName = 'app:update-event-states';

    /** @var EntityManagerInterface */
    private $em;

    private $logger;

    /** @var EventStateHelper */
    private $stateHelper;

    /**
     * On utilise l'injection de dépendance pour récupérer plein de classes utiles
     * UpdateEventStatesCommand constructor.
     * @param EntityManagerInterface $em
     * @param EventStateHelper $stateHelper
     * @param LoggerInterface $logger
     * @param string|null $libelle
     */
    public function __construct(
        EntityManagerInterface $em,
        EventStateHelper $stateHelper,
        LoggerInterface $logger,
        string $libelle = null
    )
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->stateHelper = $stateHelper;
        parent::__construct($libelle);
    }

    protected function configure()
    {
        $this
            ->setDescription('Met à jour les états des sorties')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->info('mise à jour de l\'état des sorties');

        $io = new SymfonyStyle($input, $output);

        //on charge carrément tous les sorties
        $sortieRepo = $this->em->getRepository(Sortie::class);
        $sorties = $sortieRepo->findBy([]);

        //on les parcourt...
        foreach($sorties as $sortie) {
            //voir src/EventHelper/EventStateHelper.php pour ces fonctions...

            //doit-être changé en "closed" ?
            if ($this->stateHelper->shouldChangeStateToClosed($sortie)){
                //change en closed
                $this->stateHelper->changeEventState($sortie, "fermée");
                //message pour la console et pour les logs
                $message = $sortie->getId() . " " . $sortie->getNom() . " : statut changé en fermée";
                //écrit le message dans la console
                $io->writeln($message);
                //puis dans les logs
                $this->logger->info($message);
                continue;
            }

            if ($this->stateHelper->shouldChangeStateToOngoing($sortie)){
                $this->stateHelper->changeEventState($sortie, "en cours");
                $message = $sortie->getId() . " " . $sortie->getNom() . " : statut changé en en cours";
                $io->writeln($message);
                $this->logger->info($message);
                continue;
            }

            if ($this->stateHelper->shouldChangeStateToEnded($sortie)){
                $this->stateHelper->changeEventState($sortie, "terminée");
                $message = $sortie->getId() . " " . $sortie->getNom() . " : statut changé en terminée";
                $io->writeln($message);
                $this->logger->info($message);
                continue;
            }

            if ($this->stateHelper->shouldChangeStateToArchived($sortie)){
                $this->stateHelper->changeEventState($sortie, "archivée");
                $message = $sortie->getId() . " " . $sortie->getNom() . " : statut changé en archivée";
                $io->writeln($message);
                $this->logger->info($message);
                continue;
            }
        }

        $io->success("OK c'est fait !");
        $this->logger->info('mise à jour de l\'état des sorties terminé');

        return 0;
    }
}
