<?php

namespace Lci\BoilerBoxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="equipementBA")
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Repository\EquipementBATicketRepository")
 */
class EquipementBATicket
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
    private $numeroDeSerie;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $denomination;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $autreDenomination;

    /**
     * @ORM\Column(type="date")
     */
    private $anneeDeConstruction;

    /**
     * @ORM\ManyToOne(targetEntity="Lci\BoilerBoxBundle\Entity\SiteBA", inversedBy="equipementBATickets")
     */
    private $siteBA;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroDeSerie(): ?string
    {
        return $this->numeroDeSerie;
    }

    public function setNumeroDeSerie(string $numeroDeSerie): self
    {
        $this->numeroDeSerie = $numeroDeSerie;

        return $this;
    }

    public function getDenomination(): ?string
    {
        return $this->denomination;
    }

    public function setDenomination(string $denomination): self
    {
        $this->denomination = $denomination;

        return $this;
    }

    public function getAutreDenomination(): ?string
    {
        return $this->autreDenomination;
    }

    public function setAutreDenomination(string $autreDenomination): self
    {
        $this->autreDenomination = $autreDenomination;

        return $this;
    }

    public function getAnneeDeConstruction(): ?\DateTimeInterface
    {
        return $this->anneeDeConstruction;
    }

    public function setAnneeDeConstruction(\DateTimeInterface $anneeDeConstruction): self
    {
        $this->anneeDeConstruction = $anneeDeConstruction;

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
