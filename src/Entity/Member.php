<?php
// src/Entity/Member.php

namespace App\Entity;

use App\Repository\MemberRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MemberRepository::class)]
#[Vich\Uploadable]                                          // ← AJOUT
class Member
{
    public const CATEGORY_BUREAU_PRESIDENT = 'bureau_president';
    public const CATEGORY_BUREAU_MEMBER = 'bureau_member';
    public const CATEGORY_ANIMATEUR = 'animateur';
    public const CATEGORY_WEBMESTRE = 'webmestre';
    public const CATEGORY_PRESIDENT_HONNEUR = 'president_honneur';

    public const CATEGORIES = [
        'Président du Bureau' => self::CATEGORY_BUREAU_PRESIDENT,
        'Membre du Bureau' => self::CATEGORY_BUREAU_MEMBER,
        'Animateur' => self::CATEGORY_ANIMATEUR,
        'Webmestre' => self::CATEGORY_WEBMESTRE,
        'Président d\'honneur' => self::CATEGORY_PRESIDENT_HONNEUR,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $role = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoName = null;

    #[Vich\UploadableField(mapping: 'member_photo', fileNameProperty: 'photoName')]   // ← AJOUT
    #[Assert\Image(
        maxSize: '2M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        mimeTypesMessage: 'Veuillez télécharger une image valide (JPG, PNG ou WebP)'
    )]
    private ?File $photoFile = null;                                                  // ← AJOUT

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $quote = null;

    #[ORM\Column(length: 50)]
    private ?string $category = null;

    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    #[ORM\Column(type: 'datetime', nullable: true)]                                   // ← AJOUT
    private ?\DateTimeInterface $updatedAt = null;                                     // ← AJOUT

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }
    public function setRole(string $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }
    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPhotoName(): ?string
    {
        return $this->photoName;
    }
    public function setPhotoName(?string $photoName): static
    {
        $this->photoName = $photoName;
        return $this;
    }

    // ↓ AJOUT — remplace l'ancienne gestion manuelle du fichier
    public function getPhotoFile(): ?File
    {
        return $this->photoFile;
    }

    public function setPhotoFile(?File $photoFile = null): static
    {
        $this->photoFile = $photoFile;

        if (null !== $photoFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }
    // ↑ AJOUT

    public function getQuote(): ?string
    {
        return $this->quote;
    }
    public function setQuote(?string $quote): static
    {
        $this->quote = $quote;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }
    public function setCategory(string $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }
    public function setPosition(int $position): static
    {
        $this->position = $position;
        return $this;
    }

    // ↓ AJOUT
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }
    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
    // ↑ AJOUT

    public function hasPhoto(): bool
    {
        return !empty($this->photoName);
    }
}
