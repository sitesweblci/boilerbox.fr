<?php

namespace Lci\BoilerBoxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity
 * @ORM\Table(name="equipementBA", uniqueConstraints={@UniqueConstraint(name="uniq_idx", columns={"numeroDeSerie", "denomination"})})
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Repository\EquipementBATicketRepository")
 * @UniqueEntity(
 *    fields={"numeroDeSerie", "denomination"},
 *    errorPath="numeroDeSerie",
 *    message="Cet équipement existe déjà"
 * )
 */
class EquipementBATicket
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"groupContact", "groupEquipement"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"groupEquipement"})
     */
    private $numeroDeSerie;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"groupContact", "groupEquipement"})
     */
    private $denomination;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"groupEquipement"})
     */
    private $autreDenomination;

    /**
     * @ORM\Column(type="date")
     * @Groups({"groupEquipement"})
     */
    private $anneeDeConstruction;

    /**
     * @ORM\ManyToOne(targetEntity="Lci\BoilerBoxBundle\Entity\SiteBA", cascade={"persist"}, inversedBy="equipementBATickets")
     * @ORM\JoinColumn(name="siteBA_id", referencedColumnName="id", nullable=false)
     * @Groups({"groupEquipement"})
     */
    private $siteBA;

	/**
     * @ORM\ManyToMany(targetEntity="Lci\BoilerBoxBundle\Entity\BonsAttachement", mappedBy="equipementBATicket")
     */
    private $bonsAttachement;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id)
    {
       	$this->id = $id;

		return $this;
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
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->bonsAttachement = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add bonsAttachement.
     *
     * @param \Lci\BoilerBoxBundle\Entity\BonsAttachement $bonsAttachement
     *
     * @return EquipementBATicket
     */
    public function addBonsAttachement(\Lci\BoilerBoxBundle\Entity\BonsAttachement $bonsAttachement)
    {
        $this->bonsAttachement[] = $bonsAttachement;

        return $this;
    }

    /**
     * Remove bonsAttachement.
     *
     * @param \Lci\BoilerBoxBundle\Entity\BonsAttachement $bonsAttachement
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeBonsAttachement(\Lci\BoilerBoxBundle\Entity\BonsAttachement $bonsAttachement)
    {
        return $this->bonsAttachement->removeElement($bonsAttachement);
    }

    /**
     * Get bonsAttachement.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBonsAttachement()
    {
        return $this->bonsAttachement;
    }
}
