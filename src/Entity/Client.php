<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClientRepository")
 */
class Client
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nomClient;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenomClient;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Transaction", mappedBy="client")
     */
    private $transaction;

    /**
     * @ORM\Column(type="integer")
     */
    private $telClient;

    /**
     * @ORM\Column(type="integer")
     */
    private $telRecepteur;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom_Recepteur;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenom_Recepteur;

    public function __construct()
    {
        $this->transaction = new ArrayCollection();
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

    /**
     * @return Collection|Transaction[]
     */
    public function getTransaction(): Collection
    {
        return $this->transaction;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transaction->contains($transaction)) {
            $this->transaction[] = $transaction;
            $transaction->setClient($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transaction->contains($transaction)) {
            $this->transaction->removeElement($transaction);
            // set the owning side to null (unless already changed)
            if ($transaction->getClient() === $this) {
                $transaction->setClient(null);
            }
        }

        return $this;
    }

    public function getTelClient(): ?int
    {
        return $this->telClient;
    }

    public function setTelClient(int $telClient): self
    {
        $this->telClient = $telClient;

        return $this;
    }

    public function getTelRecepteur(): ?int
    {
        return $this->telRecepteur;
    }

    public function setTelRecepteur(int $telRecepteur): self
    {
        $this->telRecepteur = $telRecepteur;

        return $this;
    }

    /**
     * Get the value of nomClient
     */ 
    public function getNomClient()
    {
        return $this->nomClient;
    }

    /**
     * Set the value of nomClient
     *
     * @return  self
     */ 
    public function setNomClient($nomClient)
    {
        $this->nomClient = $nomClient;

        return $this;
    }

    /**
     * Get the value of prenomClient
     */ 
    public function getPrenomClient()
    {
        return $this->prenomClient;
    }

    /**
     * Set the value of prenomClient
     *
     * @return  self
     */ 
    public function setPrenomClient($prenomClient)
    {
        $this->prenomClient = $prenomClient;

        return $this;
    }

    public function getNomRecepteur(): ?string
    {
        return $this->nom_Recepteur;
    }

    public function setNomRecepteur(string $nom_Recepteur): self
    {
        $this->nom_Recepteur = $nom_Recepteur;

        return $this;
    }

    public function getPrenomRecepteur(): ?string
    {
        return $this->prenom_Recepteur;
    }

    public function setPrenomRecepteur(string $prenom_Recepteur): self
    {
        $this->prenom_Recepteur = $prenom_Recepteur;

        return $this;
    }
}
