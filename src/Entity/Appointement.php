<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\AppointementRepository")
 */
class Appointement
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"patient"})
     */
    public $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"patient"})
     * @Assert\NotBlank
     */
    private $motif;

    /**
     * @ORM\Column(type="string", length=10)
     * @Assert\NotBlank
     * @Groups({"patient"})
     */
    public $heureDebut;

    /**
     * @ORM\Column(type="string", length=10)
     * @Assert\NotBlank
     * @Groups({"patient"})
     */
    private $heureFin;

    /**
     * @ORM\Column(type="string", length=12)
     * @Assert\NotBlank
     * @Groups({"patient"})
     */
    public $date;

    /**
     * @ORM\ManyToOne(targetEntity=Patient::class, inversedBy="appointements")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"patient"})
     */
    private $patient;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="appointements")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"patient"})
     */
    public $medecin;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"patient"})
     */
    public $isEnabled;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(string $motif): self
    {
        $this->motif = $motif;

        return $this;
    }

    public function getHeure(): ?string
    {
        return $this->heure;
    }

    public function setHeure(string $heure): self
    {
        $this->heure = $heure;

        return $this;
    }

    public function getHeureDebut(): ?string
    {
        return $this->heureDebut;
    }

    public function setHeureDebut(string $heureDebut): self
    {
        $this->heureDebut = $heureDebut;

        return $this;
    }

    public function getHeureFin(): ?string
    {
        return $this->heureFin;
    }

    public function setHeureFin(string $heureFin): self
    {
        $this->heureFin = $heureFin;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): self
    {
        $this->patient = $patient;

        return $this;
    }

    public function getMedecin(): ?User
    {
        return $this->medecin;
    }

    public function setMedecin(?User $medecin): self
    {
        $this->medecin = $medecin;

        return $this;
    }

    public function getIsEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }
}
