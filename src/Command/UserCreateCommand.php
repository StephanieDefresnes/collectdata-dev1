<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserCreateCommand extends Command
{
    protected static $defaultName = 'app:user:create';
    
    /**
     *
     * @var EntityManagerInterface
     */
    private $em;
    
    /**
     *
     * @var UserRepository
     */
    private $userRepository;

    /**
     *
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em, UserRepository $userRepository)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->em = $em;
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    // Questions list ( type Symfony\Component\Console\Question\Question; )
    protected function configure()
    {
        $this
            ->setDescription('Create a user.')
            ->addArgument('name', InputArgument::REQUIRED, 'The name')
            ->addArgument('email', InputArgument::REQUIRED, 'The email')
            ->addArgument('password', InputArgument::REQUIRED, 'The password')
            ->addOption('super-admin', null, InputOption::VALUE_NONE, 'Set the user as super admin')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Set the user as admin')
            ->addOption('moderator', null, InputOption::VALUE_NONE, 'Set the user as moderator')
            ->addOption('inactive', null, InputOption::VALUE_NONE, 'Set the user as inactive')
            ->setHelp(implode("\n", [
                'The <info>app:user:create</info> command creates a user:',
                '<info>php %command.full_name% John</info>',
                'This interactive shell will ask you for an email and then a password.',
                'You can alternatively specify the email and password as the second and third arguments:',
                '<info>php %command.full_name% Name email@domain.com add_a_password</info>',
                'You can create a super admin via the super-admin flag:',
                '<info>php %command.full_name% --super-admin</info>',
                'You can create an admin via the admin flag:',
                '<info>php %command.full_name% --admin</info>',
                'Default role is ROLE_USER',
                'You can create an inactive user (will not be able to log in):',
                '<info>php %command.full_name% --inactive</info>',
            ]))
        ;
    }
    
    // Interact with user console
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questions = [];
        
        // Add name
        if (!$input->getArgument('name')) {
            $question = new Question('Please enter the name:');
            $question->setValidator(function ($user_name) {
                
                // Validate that the string is not empty 
                // Otherwise we throw an exception to request name
                if (empty($user_name)) {
                    throw new \Exception('Name can not be empty');
                }
                return $user_name;
            });
            $questions['name'] = $question;
        }

        // Add email
        if (!$input->getArgument('email')) {
            $question = new Question('Please enter an email:');
            $question->setValidator(function ($email) {
                
                // Validate that the string is not empty 
                // Otherwise we throw an exception to request email address
                if (empty($email)) {
                    throw new \Exception('Email can not be empty');
                }
                // Validate that the user with this address exists or not
                if ($this->userRepository->findOneByEmail($email)) {
                    throw new \Exception('Email is already used');
                }
                return $email;
            });
            $questions['email'] = $question;
        }

        // Add password
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
    }

    // Create user
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $user = new User();
        $user
            ->setName($input->getArgument('name'))
            ->setEmail($email)
            ->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user,
                    $input->getArgument('password')
                )
            )
            ->setDateCreate(new \DateTime());
        
        // Options role
        if ($input->getOption('super-admin')) {
            $user->setRoles(['ROLE_SUPER_ADMIN']);
        } elseif ($input->getOption('admin')) {
            $user->setRoles(['ROLE_ADMIN']);
        } elseif ($input->getOption('moderator')) {
            $user->setRoles(['ROLE_MODERATOR']);
        } else {
            $user->setRoles(['ROLE_USER']);
        }
        
        // Options activation
        if ($input->getOption('inactive')) {
            $user->setEnable(false);
        } else {
            $user->setEnable(true);
        }

        $this->em->persist($user);
        $this->em->flush();

        $io->success(sprintf('Created user with email %s.', $email));

        return 0;
    }
}
