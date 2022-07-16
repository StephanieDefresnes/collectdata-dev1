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
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserChangePasswordCommand extends Command
{
    protected static $defaultName = 'app:user:change-password';
    
    private $em;
    private $passwordEncoder;
    private $userRepository;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em, UserRepository $userRepository)
    {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    // Questions list ( type Symfony\Component\Console\Question\Question; )
    protected function configure()
    {
        $this
            ->setDescription('Change the password of a user.')
            ->addArgument('email', InputArgument::REQUIRED, 'The email')
            ->addArgument('password', InputArgument::REQUIRED, 'The new password')
            ->setHelp(implode("\n", [
                'The <info>app:user:change-password</info> command changes the password of a user:',
                '<info>php %command.full_name% email@domain.com</info>',
                'This interactive shell will first ask you for a password.',
                'You can alternatively specify the password as a second argument:',
                '<info>php %command.full_name% email@domain.com change_this_password</info>',
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
                    throw new \Exception('Email can not be empty');
                }
                // Validate that the user with this address exists or not
                if (!$this->userRepository->findOneByEmail($email)) {
                    throw new \Exception('No user found with this email');
                }
                return $email;
            });
            $questions['email'] = $question;
        }

        // CHange password
        if (!$input->getArgument('password')) {
            $question = new Question('Please choose a password:');
            $question->setValidator(function ($password) {
                // Validate that the string is not empty 
                // Otherwise we throw an exception to request password
                if (empty($password)) {
                    throw new \Exception('Password can not be empty');
                }
                return $password;
            });
            $question->setHidden(true);
            $questions['password'] = $question;
        }

        // Start loop to run questions
        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }

    // Change password user
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $user = $this->userRepository->findOneByEmail($email);
        
        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                $input->getArgument('password')
            )
        );        
        $this->em->flush();        
        $io->success(sprintf('Changed password for user %s.', $email));
        return 0;
    }
}
