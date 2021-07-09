<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\OrdonnanceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=OrdonnanceRepository::class)
 */
class Ordonnance
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"patient"})
     */
    public $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"patient"})
     */
    private $medicament;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"patient"})
     */
    private $quantite;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"patient"})
     */
    private $dosage;

    /**
     * @ORM\Column(type="string", length=10)
     * @Groups({"patient"})
     */
    private $numero;

    /**
     * @ORM\OneToOne(targetEntity=PatientData::class, mappedBy="ordonnance", cascade={"persist", "remove"})
     * @Groups({"patient"})
     */
    private $patientData;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMedicament(): ?string
    {
        return $this->medicament;
    }

    public function setMedicament(string $medicament): self
    {
        $this->medicament = $medicament;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getDosage(): ?string
    {
        return $this->dosage;
    }

    public function setDosage(string $dosage): self
    {
        $this->dosage = $dosage;

        return $this;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getPatientData(): ?PatientData
    {
        return $this->patientData;
    }

    public function setPatientData(PatientData $patientData): self
    {
        // set the owning side of the relation if necessary
        if ($patientData->getOrdonnance() !== $this) {
            $patientData->setOrdonnance($this);
        }

        $this->patientData = $patientData;

        return $this;
    }
}
