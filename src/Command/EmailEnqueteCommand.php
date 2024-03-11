<?php

namespace App\Command;

use DateTime;
use App\Entity\Reservation;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'emailEnqueteCommand',
    description: 'Envoi un email (enquete de satisfaction à la fin de la periode de gardiennage)',
)]
class EmailEnqueteCommand extends Command
{

    protected static $defaultName = 'app:email-enquete';
    private $mailer;
    private $entityManager;

    public function __construct(MailerInterface $mailer, EntityManagerInterface $entityManager)
    {
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entityManager = $this->entityManager;
        $mailer = $this->mailer;


        // récupére les reservations qui sont terminées et qui n'ont pas encore reçu l'enquete
        $reservations = $entityManager->getRepository(Reservation::class)
        ->createQueryBuilder('r')
        ->where('r.dateFin <= :today')
        ->andWhere('r.is_sent_enquete = false')
        ->setParameter('today', new DateTime())
        ->getQuery()
        ->getResult();
    
        
        foreach ($reservations as $reservation) {
            $UserEmail = $reservation->getMaitre()->getUtilisateur()->getEmail();

            $email = new Email();
            $email->from('enquete@gardiens2pattes.com');
            $email->to($UserEmail);
            $email->subject('Enquete de satisfaction');
            $email->html('<div style="background-color: #f7e9d6; padding:16px">
            <h1 style="color: #7b3c04;">Votre période de gardiennage vient de s\'achever. Nous ésperont que vous êtes satisfait de nos service</p>
            <p>Pouvez-vous prendre un instant afin de répondre à notre </p><a style="color: #7b3c55;" href=\'https://fr.surveymonkey.com/r/SRWCCDT\'>enquete de satisfaction</a>
            <p>Nous vous remercions et éspéront vous revoir très vite sur le site</p>
            </div>');

            // Marquer la réservation comme notifiée pour éviter l'envoi répété de l'e-mail
            $reservation->setIsSentEnquete(true);

            // Envoyer l'e-mail
            $mailer->send($email);

            $entityManager->persist($reservation);
        }
        
        $entityManager->flush();
    
        return Command::SUCCESS;
    }
    
}

// lancer la commande:
//  php bin/console EmailEnqueteCommand