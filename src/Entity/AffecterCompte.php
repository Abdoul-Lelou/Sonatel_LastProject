<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\AffecterCompteRepository")
 */
class AffecterCompte
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $dateDebut;

    /**
     * @ORM\Column(type="date")
     */
    private $dateFin;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="affecterCompte")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="affecterComptes")
     */
    private $userAffect;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Compte", inversedBy="affecterComptes")
     */
    private $compte;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="affecterCompte")
     */
    private $affecter;

    
    public function __construct()
    {
        $this->user = new ArrayCollection();
        $this->comptes = new ArrayCollection();
        $this->affecter = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function getUserAffect(): ?User
    {
        return $this->userAffect;
    }

    public function setUserAffect(?User $userAffect): self
    {
        $this->userAffect = $userAffect;

        return $this;
    }

    public function getCompte(): ?Compte
    {
        return $this->compte;
    }

    public function setCompte(?Compte $compte): self
    {
        $this->compte = $compte;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getAffecter(): Collection
    {
        return $this->affecter;
    }

    public function addAffecter(User $affecter): self
    {
        if (!$this->affecter->contains($affecter)) {
            $this->affecter[] = $affecter;
            $affecter->setAffecterCompte($this);
        }

        return $this;
    }

    public function removeAffecter(User $affecter): self
    {
        if ($this->affecter->contains($affecter)) {
            $this->affecter->removeElement($affecter);
            // set the owning side to null (unless already changed)
            if ($affecter->getAffecterCompte() === $this) {
                $affecter->setAffecterCompte(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return (string) $this->getId();
    }
}
