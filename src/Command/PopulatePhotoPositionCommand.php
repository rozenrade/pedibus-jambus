<?php

// src/Command/PopulatePhotoPositionCommand.php

namespace App\Command;

use App\Repository\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:populate-photo-position')]
class PopulatePhotoPositionCommand extends Command
{
    public function __construct(
        private AlbumRepository $albumRepository,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $albums = $this->albumRepository->findAll();

        foreach ($albums as $album) {
            $photos = $album->getPhotos()->toArray();

            usort($photos, fn($a, $b) => $b->getCreatedAt() <=> $a->getCreatedAt());

            foreach ($photos as $index => $photo) {
                $photo->setPosition($index);
            }
        }

        $this->em->flush();

        $output->writeln('Positions peuplées avec succès.');
        return Command::SUCCESS;
    }
}