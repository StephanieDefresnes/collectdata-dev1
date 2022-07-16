<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\Question;

class UserActivateCommand extends Command
{
    protected static $defaultName = 'app:user:activate';
    
    private $em;
    private $userRepository;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    // Questions list ( type Symfony\Component\Console\Question\Question; )
    protected function configure()
    {
        $this
            ->setDescription('Activate a user')
            ->addArgument('email', InputArgument::REQUIRED, 'The email')
            ->setHelp(implode("\n", [
                'The <info>app:user:activate</info> command activates a user (so they will be able to log in):',
                '<info>php %command.full_name% email@domain.com/info>',
            ]))
        ;
    }
    
    // Interact with user console
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questions = [];

        if (!$input->getArgument('email')) {
            $question = new Question('Please give the email:');
            $question->setValidator(function ($email) {
                
                // Validate that the string is not empty 
                // Otherwise we throw an exception to request email address
                if (empty($email)) {
                    throw new \Exception('email can not be empty');
                }
                // Validate that the user with this address exists or not
                if (!$this->userRepository->findOneByEmail($email)) {
                    throw new \Exception('No user found with this email');
                }
                return $email;
            });
            $questions['email'] = $question;
        }
        
        // Start loop to run questions
        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }

    // Activate user
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $user = $this->userRepository->findOneByEmail($email);
        
        $user->setEnabled(true);
        $this->em->flush();
        
        $io->success(sprintf('User "%s" has been activated.', $email));
        return 0;
    }
}
