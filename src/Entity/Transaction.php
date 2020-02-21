<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\TransactionRepository")
 */
class Transaction implements AdvancedUserInterface
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
    private $code;

    /**
     * @ORM\Column(type="integer")
     */
    private $frais;

    /**
     * @ORM\Column(type="integer")
     */
    private $commissionSysteme;

    /**
     * @ORM\Column(type="integer")
     */
    private $commissionEtat;


    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="date")
     * @ORM\JoinColumn(nullable=false)
     */
    private $date_depot;
    
     
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Compte", inversedBy="transactions")
     *  @ORM\JoinColumn(nullable=false)
     */
    private $deposer;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Compte", inversedBy="transactions")
     */
    private $retirer;

    /**
     * @ORM\Column(type="float")
     */
    private $commission_envoie;

    /**
     * @ORM\Column(type="float")
     */
    private $montant;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="transaction")
     * @ORM\JoinColumn(nullable=false)
     */
    private $client;

    /**
     * @ORM\Column(type="integer")
     */
    private $commission_retait;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_retrait;

    
    public function __construct()
    {
        
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getFrais(): ?int
    {
        return $this->frais;
    }

    public function setFrais(int $frais): self
    {
        $this->frais = $frais;

        return $this;
    }

    public function getCommissionSysteme(): ?int
    {
        return $this->commissionSysteme;
    }

    public function setCommissionSysteme(int $commissionSysteme): self
    {
        $this->commissionSysteme = $commissionSysteme;

        return $this;
    }

    public function getCommissionEtat(): ?int
    {
        return $this->commissionEtat;
    }

    public function setCommissionEtat(int $commissionEtat): self
    {
        $this->commissionEtat = $commissionEtat;

        return $this;
    }

   
    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function isAccountNonExpired(){
        return true;
    }
    public function isAccountNonLocked(){
        return true;
    }
    public function isCredentialsNonExpired(){
        return true;
    }
    public function isEnabled(){
        return $this->isActive;
    }
    
    
    public function getSalt(){}
    public function eraseCredentials(){}
    public function getPassword(){}
    public function getUsername(){}
    public function getRoles(){}
  

    public function getDeposer(): ?Compte
    {
        return $this->deposer;
    }

    public function setDeposer(?Compte $deposer): self
    {
        $this->deposer = $deposer;

        return $this;
    }

    public function getRetirer(): ?Compte
    {
        return $this->retirer;
    }

    public function setRetirer(?Compte $retirer): self
    {
        $this->retirer = $retirer;

        return $this;
    }

    public function getCommissionEnvoie(): ?float
    {
        return $this->commission_envoie;
    }

    public function setCommissionEnvoie(float $commission_envoie): self
    {
        $this->commission_envoie = $commission_envoie;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    

    /**
     * Get the value of date_depot
     */ 
    public function getDate_depot()
    {
        return $this->date_depot;
    }

    /**
     * Set the value of date_depot
     *
     * @return  self
     */ 
    public function setDate_depot($date_depot)
    {
        $this->date_depot = $date_depot;

        return $this;
    }

    

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getCommissionRetait(): ?int
    {
        return $this->commission_retait;
    }

    public function setCommissionRetait(int $commission_retait): self
    {
        $this->commission_retait = $commission_retait;

        return $this;
    }

    public function getDateRetrait(): ?\DateTimeInterface
    {
        return $this->date_retrait;
    }

    public function setDateRetrait(?\DateTimeInterface $date_retrait): self
    {
        $this->date_retrait = $date_retrait;

        return $this;
    }
}