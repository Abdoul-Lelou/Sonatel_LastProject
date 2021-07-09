<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\PatientRepository")
 * @UniqueEntity("tel")
 * @UniqueEntity("matricule")
 */
class Patient
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"patient"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"patient"})
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"patient"})
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"patient"})
     */
    private $adresse;

    /**
     * @ORM\Column(type="string", length=10)
     * @Groups({"patient"})
     */
    private $sexe;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"patient"})
     */
    private $age;

    /**
     * @ORM\Column(type="string", length=10)
     * @Groups({"patient"})
     */
    private $matricule;

    /**
     * @ORM\Column(type="integer",unique=true)
     * @Groups({"patient"})
     * @Assert\Email()
     */
    private $tel;

    /**
     * @ORM\OneToMany(targetEntity=Appointement::class, mappedBy="patient", orphanRemoval=true)
     * @Groups({"patient"})
     */
    private $appointements;

    /**
     * @ORM\OneToMany(targetEntity=PatientData::class, mappedBy="patient", orphanRemoval=true)
     * @Groups({"patient"})
     */
    private $patientData;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"patient"})
     */
    private $isVisit;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="patient")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"patient"})
     */
    private $medecin;

//    /**
    //    * @ORM\ManyToOne(targetEntity=User::class, inversedBy="patients")
    //    * @ORM\JoinColumn(nullable=false)
    //   * @Groups({"patient"})
    //   */
    //  private $medecin;

    public function __construct()
    {
        $this->appointements = new ArrayCollection();
        $this->patientData = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    public function setSexe(string $sexe): self
    {
        $this->sexe = $sexe;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getMatricule(): ?string
    {
        return $this->matricule;
    }

    public function setMatricule(string $matricule): self
    {
        $this->matricule = $matricule;

        return $this;
    }

    public function getTel(): ?int
    {
        return $this->tel;
    }

    public function setTel(int $tel): self
    {
        $this->tel = $tel;

        return $this;
    }

    /**
     * @return Collection|Appointement[]
     */
    public function getAppointements(): Collection
    {
        return $this->appointements;
    }

    public function addAppointement(Appointement $appointement): self
    {
        if (!$this->appointements->contains($appointement)) {
            $this->appointements[] = $appointement;
            $appointement->setPatient($this);
        }

        return $this;
    }

    public function removeAppointement(Appointement $appointement): self
    {
        if ($this->appointements->removeElement($appointement)) {
            // set the owning side to null (unless already changed)
            if ($appointement->getPatient() === $this) {
                $appointement->setPatient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PatientData[]
     */
    public function getPatientData(): Collection
    {
        return $this->patientData;
    }

    public function addPatientData(PatientData $patientData): self
    {
        if (!$this->patientData->contains($patientData)) {
            $this->patientData[] = $patientData;
            $patientData->setPatient($this);
        }

        return $this;
    }

    public function removePatientData(PatientData $patientData): self
    {
        if ($this->patientData->removeElement($patientData)) {
            // set the owning side to null (unless already changed)
            if ($patientData->getPatient() === $this) {
                $patientData->setPatient(null);
            }
        }

        return $this;
    }

  

    public function getIsVisit(): ?bool
    {
        return $this->isVisit;
    }

    public function setIsVisit(bool $isVisit): self
    {
        $this->isVisit = $isVisit;

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
}
