<?php

namespace Lci\BoilerBoxBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\ORM\Mapping as ORM;

/**
 * Contact
 * @ORM\Entity
 * @ORM\Table(name="contact", uniqueConstraints={@UniqueConstraint(name="uniq_contact", columns={"nom", "prenom", "siteBA_id"})}) 
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Repository\ContactRepository")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(
 *    fields={"nom", "prenom", "siteBA"},
 *    errorPath="nom",
 *    message="Le contact existe déja."
 * )
*/
class Contact
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
    private $nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $telephone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mail;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fonction;

    /**
     * @ORM\Column(type="boolean")
     */
    private $statut;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_maj;

    /**
     * @ORM\ManyToOne(targetEntity="Lci\BoilerBoxBundle\Entity\SiteBA", inversedBy="contacts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $siteBA;


	public function __construct() {
		$this->date_maj = new \Datetime();
		$this->prenom = '';
		$this->statut = true;
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

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getFonction(): ?string
    {
        return $this->fonction;
    }

    public function setFonction(string $fonction): self
    {
        $this->fonction = $fonction;

        return $this;
    }

    public function getStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(bool $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDateMaj(): ?\DateTimeInterface
    {
        return $this->date_maj;
    }

    public function setDateMaj(\DateTimeInterface $date_maj): self
    {
        $this->date_maj = $date_maj;

        return $this;
    }

    public function getSiteBA(): ?SiteBA
    {
        return $this->siteBA;
    }

    public function setSiteBA(?SiteBA $siteBA): self
    {
        $this->siteBA = $siteBA;

        return $this;
    }

}
