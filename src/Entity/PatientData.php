<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\PatientDataRepository")
 */
class PatientData
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
     */
    private $symptome;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"patient"})
     */
    private $poids;

    /**
     * @ORM\Column(type="string", length=100)
     * @Groups({"patient"})
     */
    private $constat;

    /**
     * @ORM\Column(type="string", length=10)
     * @Groups({"patient"})
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=10)
     * @Groups({"patient"})
     */
    private $heure;

    /**
     * @ORM\Column(type="float")
     * @Groups({"patient"})
     */
    private $taille;

    /**
     * @ORM\ManyToOne(targetEntity=Patient::class, inversedBy="patientData")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"patient"})
     */
    private $patient;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="patientData")
     * @ORM\JoinColumn(nullable=false)
     */
    private $medecin;

    /**
     * @ORM\Column(type="string", length=5)
     * @Groups({"patient"})
     */
    private $groupe;

    /**
     * @ORM\OneToOne(targetEntity=Ordonnance::class, inversedBy="patientData", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"patient"})
     */
    private $ordonnance;

    public function __construct()
    {
        $this->ordonnances = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSymptome(): ?string
    {
        return $this->symptome;
    }

    public function setSymptome(string $symptome): self
    {
        $this->symptome = $symptome;

        return $this;
    }

    public function getPoids(): ?int
    {
        return $this->poids;
    }

    public function setPoids(int $poids): self
    {
        $this->poids = $poids;

        return $this;
    }

    public function getConstat(): ?string
    {
        return $this->constat;
    }

    public function setConstat(string $constat): self
    {
        $this->constat = $constat;

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

    public function getHeure(): ?string
    {
        return $this->heure;
    }

    public function setHeure(string $heure): self
    {
        $this->heure = $heure;

        return $this;
    }

    public function getTaille(): ?float
    {
        return $this->taille;
    }

    public function setTaille(float $taille): self
    {
        $this->taille = $taille;

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

    public function getGroupe(): ?string
    {
        return $this->groupe;
    }

    public function setGroupe(string $groupe): self
    {
        $this->groupe = $groupe;

        return $this;
    }

    public function getOrdonnance(): ?Ordonnance
    {
        return $this->ordonnance;
    }

    public function setOrdonnance(Ordonnance $ordonnance): self
    {
        $this->ordonnance = $ordonnance;

        return $this;
    }
}
