<?php
//src/Lci/BoilerBoxBundle/Entity/SiteBA.php

namespace Lci\BoilerBoxBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * SiteBA
 * @ORM\Entity
 * @ORM\Table(name="siteBA")
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Entity\SiteBARepository")
 * @UniqueEntity("intitule")
 * @ORM\HasLifecycleCallbacks()
*/
class SiteBA
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"groupContact"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotNull(message="Nom du site manquant")
     * @Groups({"groupContact"})
    */
    protected $intitule;


	/**
	 * Un site peut être la cible de plusieurs bons d'attachements
	 *
	 * @ORM\OneToMany(targetEntity="BonsAttachement", mappedBy="site")
	*/
	protected $bonsAttachement;

	
	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=true)
	*/
	protected $lienGoogle;


	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotNull(message="Veuillez entrer une adresse valide svp")
     * @Groups({"groupContact"})
	*/
	protected $adresse;


	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=true)
     * @Groups({"groupContact"})
	*/
	protected $informationsClient;

    /**
     *
     * @ORM\OneToMany(targetEntity="Lci\BoilerBoxBundle\Entity\FichierSiteBA", mappedBy="siteBA", cascade={"persist", "remove"})
     * @Groups({"groupContact"})
     *
    */
    protected $fichiersJoint;

    /**
     * @ORM\OneToMany(targetEntity="Lci\BoilerBoxBundle\Entity\Contact", mappedBy="siteBA", cascade={"persist", "remove"})
     * @Groups({"groupContact"})
     */
    private $contacts;

    /**
     * @ORM\OneToMany(targetEntity="Lci\BoilerBoxBundle\Entity\EquipementBATicket", mappedBy="siteBA", cascade={"persist", "remove"})
     * @Groups({"groupContact"})
     */
    private $equipementBATickets;



    /**
     * Constructor
     */
    public function __construct()
    {
        $this->bonsAttachement 		= new \Doctrine\Common\Collections\ArrayCollection();
		$this->fichiersJoint 		= new \Doctrine\Common\Collections\ArrayCollection();
  		$this->contacts 			= new ArrayCollection();
    	$this->equipementBATickets 	= new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param string $id
     * @return SiteBA
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }


    /**
     * Set intitule
     *
     * @param string $intitule
     * @return SiteBA
     */
    public function setIntitule($intitule)
    {
        $this->intitule = $intitule;

        return $this;
    }

    /**
     * Get intitule
     *
     * @return string 
     */
    public function getIntitule()
    {
        return $this->intitule;
    }

    /**
     * Add bonsAttachement
     *
     * @param \Lci\BoilerBoxBundle\Entity\BonsAttachement $bonsAttachement
     * @return SiteBA
     */
    public function addBonsAttachement(\Lci\BoilerBoxBundle\Entity\BonsAttachement $bonsAttachement)
    {
        $this->bonsAttachement[] = $bonsAttachement;

        return $this;
    }

    /**
     * Remove bonsAttachement
     *
     * @param \Lci\BoilerBoxBundle\Entity\BonsAttachement $bonsAttachement
     */
    public function removeBonsAttachement(\Lci\BoilerBoxBundle\Entity\BonsAttachement $bonsAttachement)
    {
        $this->bonsAttachement->removeElement($bonsAttachement);
    }

    /**
     * Get bonsAttachement
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBonsAttachement()
    {
        return $this->bonsAttachement;
    }

    /**
     * Set lienGoogle
     *
     * @param string $lienGoogle
     * @return SiteBA
     */
    public function setLienGoogle($lienGoogle)
    {
        $this->lienGoogle = $lienGoogle;

        return $this;
    }

    /**
     * Get lienGoogle
     *
     * @return string
     */
    public function getLienGoogle()
    {
        return $this->lienGoogle;
    }

    /**
     * Set adresse
     *
     * @param string $adresse
     * @return SiteBA
     */
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Get adresse
     *
     * @return string 
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set informationsClient
     *
     * @param string $informationsClient
     * @return SiteBA
     */
    public function setInformationsClient($informationsClient)
    {
        $this->informationsClient = $informationsClient;

        return $this;
    }

    /**
     * Get informationsClient
     *
     * @return string 
     */
    public function getInformationsClient()
    {
        return $this->informationsClient;
    }

    /**
     * Add fichiersJoint
     *
     * @param \Lci\BoilerBoxBundle\Entity\FichierSiteBA $fichiersJoint
     * @return BonsAttachement
     */
    public function addFichiersJoint(\Lci\BoilerBoxBundle\Entity\FichierSiteBA $fichiersJoint)
    {
        $this->fichiersJoint[] = $fichiersJoint;

        // On lie le fichier
        $fichiersJoint->setSiteBA($this);

        return $this;
    }

    /*
     * Remove fichiersJoint
     *
     * @param \Lci\BoilerBoxBundle\Entity\FichierSiteBA $fichiersJoint
    public function removeFichiersJoint(\Lci\BoilerBoxBundle\Entity\FichierSiteBA $fichiersJoint)
    {
        $this->fichiersJoint->removeElement($fichiersJKoint);
        //$fichiersJoint->setSiteBA(null);
    }
    */


    /**
     * Get fichiersJoint
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFichiersJoint()
    {
        return $this->fichiersJoint;
    }



    /**
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
    */
    public function setBonToFichiers(){
        foreach ($this->fichiersJoint as $fichier) {
            $fichier->setSiteBA($this);
        }
    }



	public function setFichiersJointToEmpty() {
    	$this->fichiersJoint = array();
    }




    /**
     * Remove fichiersJoint.
     *
     * @param \Lci\BoilerBoxBundle\Entity\FichierSiteBA $fichiersJoint
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeFichiersJoint(\Lci\BoilerBoxBundle\Entity\FichierSiteBA $fichiersJoint)
    {
        return $this->fichiersJoint->removeElement($fichiersJoint);
    }

    /**
     * @return Collection|Contact[]
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact): self
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts[] = $contact;
            $contact->setSiteBA($this);
        }

        return $this;
    }

    public function removeContact(Contact $contact): self
    {
        if ($this->contacts->contains($contact)) {
            $this->contacts->removeElement($contact);
            // set the owning side to null (unless already changed)
            if ($contact->getSiteBA() === $this) {
                $contact->setSiteBA(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|EquipementBATicket[]
     */
    public function getEquipementBATickets(): Collection
    {
        return $this->equipementBATickets;
    }

    public function addEquipementBATicket(EquipementBATicket $equipementBATicket): self
    {
        if (!$this->equipementBATickets->contains($equipementBATicket)) {
            $this->equipementBATickets[] = $equipementBATicket;
            $equipementBATicket->setSiteBA($this);
        }

        return $this;
    }

    public function removeEquipementBATicket(EquipementBATicket $equipementBATicket): self
    {
        if ($this->equipementBATickets->contains($equipementBATicket)) {
            $this->equipementBATickets->removeElement($equipementBATicket);
            // set the owning side to null (unless already changed)
            if ($equipementBATicket->getSiteBA() === $this) {
                $equipementBATicket->setSiteBA(null);
            }
        }

        return $this;
    }


}
