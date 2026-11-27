<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Enum\StatutCommande;
use App\Repository\CommandeRepository;
use App\State\CommandeStateProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_SERVEUR') or is_granted('ROLE_BARMAN') or is_granted('ROLE_PATRON')"
        ),
        new Get(
            security: "is_granted('ROLE_SERVEUR') or is_granted('ROLE_BARMAN') or is_granted('ROLE_PATRON')"
        ),
        new Post(
            security: "is_granted('ROLE_SERVEUR')",
            processor: CommandeStateProcessor::class,
            denormalizationContext: ['groups' => ['commande:create']]
        ),
        new Patch(
            security: "is_granted('ROLE_SERVEUR') or is_granted('ROLE_BARMAN')",
            processor: CommandeStateProcessor::class,
            denormalizationContext: ['groups' => ['commande:update']]
        ),
        new Put(
            security: "is_granted('ROLE_SERVEUR') or is_granted('ROLE_BARMAN')",
            processor: CommandeStateProcessor::class,
            denormalizationContext: ['groups' => ['commande:update']]
        ),
        new Delete(
            security: "is_granted('ROLE_PATRON')"
        ),
    ],
    normalizationContext: ['groups' => ['commande:read']],
)]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
#[ApiFilter(SearchFilter::class, properties: ['status' => 'exact'])]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['commande:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['commande:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Le numéro de table est obligatoire.')]
    #[Assert\Positive(message: 'Le numéro de table doit être un entier positif.')]
    #[Groups(['commande:read', 'commande:create', 'commande:update'])]
    private ?int $numeroTable = null;

    #[ORM\Column(type: 'string', enumType: StatutCommande::class)]
    #[Groups(['commande:read', 'commande:update'])]
    private StatutCommande $status = StatutCommande::EN_COURS_PREPARATION;

    /**
     * @var Collection<int, Boisson>
     */
    #[ORM\ManyToMany(targetEntity: Boisson::class)]
    #[Assert\Count(min: 1, minMessage: 'Une commande doit contenir au moins une boisson.')]
    #[Groups(['commande:read', 'commande:create', 'commande:update'])]
    private Collection $boissons;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['commande:read'])]
    private ?User $serveur = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['commande:read', 'commande:update'])]
    private ?User $barman = null;

    public function __construct()
    {
        $this->boissons = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->status = StatutCommande::EN_COURS_PREPARATION;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getNumeroTable(): ?int
    {
        return $this->numeroTable;
    }

    public function setNumeroTable(int $numeroTable): static
    {
        $this->numeroTable = $numeroTable;

        return $this;
    }

    public function getStatus(): StatutCommande
    {
        return $this->status;
    }

    public function setStatus(StatutCommande $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Boisson>
     */
    public function getBoissons(): Collection
    {
        return $this->boissons;
    }

    public function addBoisson(Boisson $boisson): static
    {
        if (!$this->boissons->contains($boisson)) {
            $this->boissons->add($boisson);
        }

        return $this;
    }

    public function removeBoisson(Boisson $boisson): static
    {
        $this->boissons->removeElement($boisson);

        return $this;
    }

    public function getServeur(): ?User
    {
        return $this->serveur;
    }

    public function setServeur(?User $serveur): static
    {
        $this->serveur = $serveur;

        return $this;
    }

    public function getBarman(): ?User
    {
        return $this->barman;
    }

    public function setBarman(?User $barman): static
    {
        $this->barman = $barman;

        return $this;
    }
}
