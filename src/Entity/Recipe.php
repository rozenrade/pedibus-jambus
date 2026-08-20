<?php

namespace App\Entity;

use App\Repository\RecipeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: RecipeRepository::class)]
#[Vich\Uploadable]
class Recipe
{
    public const CATEGORY_ENTREE = 'entree';
    public const CATEGORY_PLAT = 'plat';
    public const CATEGORY_DESSERT = 'dessert';
    public const CATEGORY_APERITIF = 'aperitif';
    public const CATEGORY_AUTRE = 'autre';

    public const CATEGORIES = [
        'Entrée' => self::CATEGORY_ENTREE,
        'Plat' => self::CATEGORY_PLAT,
        'Dessert' => self::CATEGORY_DESSERT,
        'Apéritif' => self::CATEGORY_APERITIF,
        'Autre' => self::CATEGORY_AUTRE,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Gedmo\Slug(fields: ['title'])]
    private ?string $slug;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    private ?string $ingredients = null;

    #[ORM\Column(type: 'text')]
    private ?string $steps = null;

    #[ORM\Column(nullable: true)]
    private ?int $servings = null;

    #[ORM\Column(length: 50)]
    private ?string $category = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoName = null;

    #[Vich\UploadableField(mapping: 'recipe_photo', fileNameProperty: 'photoName')]
    private ?File $photoFile = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

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

    public function getIngredients(): ?string
    {
        return $this->ingredients;
    }

    public function setIngredients(string $ingredients): static
    {
        $this->ingredients = $ingredients;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getIngredientsList(): array
    {
        return $this->splitLines($this->ingredients);
    }

    public function getSteps(): ?string
    {
        return $this->steps;
    }

    public function setSteps(string $steps): static
    {
        $this->steps = $steps;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getStepsList(): array
    {
        return $this->splitLines($this->steps);
    }

    public function getServings(): ?int
    {
        return $this->servings;
    }

    public function setServings(?int $servings): static
    {
        $this->servings = $servings;
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

    public function getCategoryLabel(): string
    {
        return array_search($this->category, self::CATEGORIES, true) ?: 'Autre';
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

    public function getPhotoFile(): ?File
    {
        return $this->photoFile;
    }

    public function setPhotoFile(?File $photoFile = null): void
    {
        $this->photoFile = $photoFile;

        if (null !== $photoFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function hasPhoto(): bool
    {
        return !empty($this->photoName);
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function splitLines(?string $text): array
    {
        if (empty($text)) {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', explode("\n", $text)),
            fn($line) => $line !== ''
        ));
    }

    public function __toString(): string
    {
        return $this->title ?? 'Nouvelle recette';
    }

    /**
     * Get the value of slug
     */ 
    public function getSlug()
    {
        return $this->slug;
    }
}
