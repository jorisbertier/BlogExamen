<?php

namespace App\Command;

use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:delete-content-pokemon',
    description: 'Delete all content in comments who containing "pokemon" ',
)]


class DeleteContentPokemonCommand extends Command
{
    public function __construct(
        private CommentRepository $commentRepository,
        private EntityManagerInterface $entityManager
        )
    {
        parent::__construct();
        $this->commentRepository = $commentRepository;
        $this->entityManager = $entityManager;
    }
    
    protected function configure(): void
    {
        $this
        ->setDescription('Delete content in comments who containing "pokemon" ')
        ->setHelp('This command delete all contents in comments who containing the word "pokemon" ');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $comments = $this->commentRepository->createQueryBuilder('c')
        ->where('c.content LIKE :word')
        ->setParameter('word', '%pokemon%')
        ->getQuery()
        ->getResult();

        foreach ($comments as $comment) {
            $this->entityManager->remove($comment);
        }

        $this->entityManager->flush();
        $output->writeln('Success delete comments containing the word "pokemon".');

        return Command::SUCCESS;
    }
}
