<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\TarifRepository")
 */
class Tarif
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $borne_inf;

    /**
     * @ORM\Column(type="float")
     */
    private $borne_sup;

    /**
     * @ORM\Column(type="float")
     */
    private $frais;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBorneInf(): ?float
    {
        return $this->borne_inf;
    }

    public function setBorneInf(float $borne_inf): self
    {
        $this->borne_inf = $borne_inf;

        return $this;
    }

    public function getBorneSup(): ?float
    {
        return $this->borne_sup;
    }

    public function setBorneSup(float $borne_sup): self
    {
        $this->borne_sup = $borne_sup;

        return $this;
    }

    public function getFrais(): ?float
    {
        return $this->frais;
    }

    public function setFrais(float $frais): self
    {
        $this->frais = $frais;

        return $this;
    }
}