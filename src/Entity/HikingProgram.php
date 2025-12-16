<?php

namespace App\Entity;

use App\Repository\HikingProgramRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: HikingProgramRepository::class)]
#[Vich\Uploadable]
class HikingProgram
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 4)]
    private ?int $year = null;

    #[ORM\Column(nullable: true)]
    private ?int $quarter = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    // Champ virtuel pour l'upload de fichier
    #[Vich\UploadableField(mapping: 'program_pdf', fileNameProperty: 'pdfName', size: 'pdfSize')]
    private ?File $pdfFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pdfName = null;

    #[ORM\Column(nullable: true)]
    private ?int $pdfSize = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updateAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getQuarter(): ?string
    {
        return $this->quarter;
    }

    public function setQuarter(string $quarter): static
    {
        $this->quarter = $quarter;

        return $this;
    }

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(string $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    // Méthodes pour VichUploader
    public function setPdfFile(?File $pdfFile = null): void
    {
        $this->pdfFile = $pdfFile;

        if (null !== $pdfFile) {
            $this->updateAt = new \DateTimeImmutable();
        }
    }

    public function getPdfFile(): ?File
    {
        return $this->pdfFile;
    }

    public function getPdfName(): ?string
    {
        return $this->pdfName;
    }

    public function setPdfName(?string $pdfName): static
    {
        $this->pdfName = $pdfName;

        return $this;
    }

    public function getPdfSize(): ?int
    {
        return $this->pdfSize;
    }

    public function setPdfSize(?int $pdfSize): static
    {
        $this->pdfSize = $pdfSize;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeImmutable
    {
        return $this->updateAt;
    }

    public function setUpdateAt(?\DateTimeImmutable $updateAt): static
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    // Méthode pour obtenir l'URL publique du PDF
    public function getPdfUrl(): ?string
    {
        return $this->pdfName ? '/uploads/programs/' . $this->pdfName : null;
    }

    // Méthode pour afficher l'objet
    public function __toString(): string
    {
        return $this->title ?? 'Nouveau programme de randonnée';
    }
}