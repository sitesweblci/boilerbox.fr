<?php 
// src/Lci/BoilerBoxBundle/Entity/BonsAttachement.php
namespace Lci\BoilerBoxBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * BonsAttachement
 *
 * @ORM\Entity
 * @ORM\Table(name="bon_attachement")
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Repository\BonsAttachementRepository")
 * @ORM\HasLifecycleCallbacks()
*/

class BonsAttachement {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="numero_ba", nullable=true)
	*/
	protected $numeroBA;

	/**
	 * @var string
	 *
     * @Assert\Type("string")
	 * @Assert\NotBlank(message="Veuillez indiquer le numéro de l'affaire")
     * @Assert\Length(min=4, max=7, minMessage="Format de l'affaire incorrect. Nombre de caractères minimum = 4", maxMessage="Format de l'affaire incorrect. Nombre de caractères maximum = 7")
     * 
     * @ORM\Column(type="text", name="numero_affaire")
    */
    protected $numeroAffaire;


    /**
     * @var string
     *
     * @Assert\Type("string")
     * @ORM\Column(type="text", name="nom_du_contact", nullable=true)
    */
    protected $nomDuContact;


    /**
     * Plusieurs bons d'attachements peuvent être crées par un même initiateur
     *
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"}, inversedBy="bonsAttachementInitiateur")
     * @ORM\OrderBy({"label" = "ASC"})
    */
    protected $userInitiateur;


	/**
     * Plusieurs bons d'attachements peuvent avoir plusieurs équipements 
     *
     * @ORM\ManyToMany(targetEntity="EquipementBATicket", cascade={"persist"}, inversedBy="bonsAttachement")
     * @ORM\OrderBy({"denomination" = "ASC"})
    */
    protected $equipementBATicket;



    /**
     * @var date
     *
     * @Assert\Date(message="Format incorrect. Format jj/mm/AAAA attendu.")
     * @Assert\NotBlank(message="Veuillez indiquer la date d'initiation du bon")
     *
     * @ORM\Column(type="date", name="date_initialisation")
    */
    protected $dateInitialisation;


	/**
     * @var date
	 *
	 * @Assert\Date(message="Format incorrect. Format jj/mm/AAAA attendu.")
	 *
	 * @ORM\Column(type="date", name="date_de_signature", nullable=true)
	*/
	protected $dateSignature;

    /**
     * @var date
     *
     * @Assert\Date(message="Format incorrect. Format jj/mm/AAAA attendu.")
     *
     * @ORM\Column(type="date", name="date_debut_intervention", nullable=true)
    */
    protected $dateDebutIntervention;


    /**
     * @var date
     *
     * @Assert\Date(message="Format incorrect. Format jj/mm/AAAA attendu.")
     *
     * @ORM\Column(type="date", name="date_fin_intervention", nullable=true)
    */
    protected $dateFinIntervention;



	/**
 	 * @var string
	 *
	 * @Assert\Email(checkMX="true") 
	 * 
	 * @ORM\Column(type="text", name="email_contact_client", nullable=true)
	*/
	protected $emailContactClient;


    /**
     * @var boolean
     *
     * @Assert\Type("bool")
     *
     * @ORM\Column(type="boolean", name="enquete_necessaire", options={"default"=0})
    */
    protected $enqueteNecessaire;


    /**
     * @var boolean
     *
     * @Assert\Type("bool")
     *
     * @ORM\Column(type="boolean", name="enquete_faite", options={"default"=0})
    */
    protected $enqueteFaite;


	/**
	 * @var string
	 *
	 * @Assert\Type("string")
	 *	
	 * @ORM\Column(type="text", nullable=true)
	*/
	protected $commentaires;

	/**
	 *
     * @ORM\OneToOne(targetEntity="Validation", cascade={"persist", "remove"})
	*/
	protected $validationTechnique;

    /**
     *
     * @ORM\OneToOne(targetEntity="Validation", cascade={"persist", "remove"})
    */
    protected $validationPiece;

    /**
     *
     * @ORM\OneToOne(targetEntity="Validation", cascade={"persist", "remove"})
    */
    protected $validationPieceFaite;


    /**
     *
     * @ORM\OneToOne(targetEntity="Validation", cascade={"persist", "remove"})
    */
    protected $validationSAV;

    /**
     *
     * @ORM\OneToOne(targetEntity="Validation", cascade={"persist", "remove"})
    */
    protected $validationFacturation;



	/**
	 * Plusieurs bons d'attachements peuvent cibler un site
	 *
	 * 
	 * @ORM\ManyToOne(targetEntity="SiteBA", cascade={"persist"}, inversedBy="bonsAttachement")
	*/
	protected $site;


    /**
     * Plusieurs bons d'attachements peuvent cibler un utilisateur
     *
     *
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"}, inversedBy="bonsAttachement")
     * @ORM\OrderBy({"label" = "ASC"})
    */
    protected $user;


    /**
     *
     * @ORM\OneToMany(targetEntity="Lci\BoilerBoxBundle\Entity\Fichier", mappedBy="bonAttachement", cascade={"persist", "remove"})
     *
    */
	protected $fichiersPdf;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="telephone_contact_client", nullable=true)
    */
    protected $telephoneContactClient;


    // Entre BOSCH, CERTUS,  EXPORT..
    /**
     * @var string
     *
     * @ORM\Column(type="string", name="service", nullable=true)
    */
    protected $service;


    // Entre mise en service, sav, rapport mensuel ...
    /**
     * @var string
     *
     * @ORM\Column(type="string", name="type_intervention")
    */
    protected $typeIntervention;


    // Entre bon attachement et ticket
    /**
     * @var string
     *
     * @ORM\Column(type="string", name="type")
    */
    protected $type;

    // Motif de création d'un ticket d'incident
    /**
     * @var string
     *
     * @ORM\Column(type="string", name="motif")
    */
    protected $motif;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
    */
    protected $cheminDossierPhotos;





	public function __construct() {
       	$this->dateInitialisation = new \Datetime();
       	$this->enqueteNecessaire = false;
       	$this->enqueteFaite = false;
    	$this->fichiersPdf = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set numeroBA
     *
     * @param string $numeroBA
     * @return BonsAttachement
     */
    public function setNumeroBA($numeroBA)
    {
        $this->numeroBA = $numeroBA;

        return $this;
    }

    /**
     * Get numeroBA
     *
     * @return string 
     */
    public function getNumeroBA()
    {
        return $this->numeroBA;
    }

    /**
     * Set numeroAffaire
     *
     * @param string $numeroAffaire
     * @return BonsAttachement
     */
    public function setNumeroAffaire($numeroAffaire)
    {
        $this->numeroAffaire = strtoupper($numeroAffaire);

        return $this;
    }

    /**
     * Get numeroAffaire
     *
     * @return string 
     */
    public function getNumeroAffaire()
    {
        return $this->numeroAffaire;
    }


    /**
     * Set dateSignature
     *
     * @param \DateTime $dateSignature
     * @return BonsAttachement
     */
    public function setDateSignature($dateSignature)
    {
        $this->dateSignature = $dateSignature;

        return $this;
    }

    /**
     * Get dateSignature
     *
     * @return \DateTime 
     */
    public function getDateSignature()
    {
        return $this->dateSignature;
    }

    /**
     * Set emailContactClient
     *
     * @param string $emailContactClient
     * @return BonsAttachement
     */
    public function setEmailContactClient($emailContactClient)
    {
        $this->emailContactClient = $emailContactClient;

        return $this;
    }

    /**
     * Get emailContactClient
     *
     * @return string 
     */
    public function getEmailContactClient()
    {
        return $this->emailContactClient;
    }

    /**
     * Set site
     *
     * @param \Lci\BoilerBoxBundle\Entity\SiteBA $site
     * @return BonsAttachement
     */
    public function setSite(\Lci\BoilerBoxBundle\Entity\SiteBA $site = null)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site
     *
     * @return \Lci\BoilerBoxBundle\Entity\SiteBA 
     */
    public function getSite()
    {
        return $this->site;
    }


    /**
     * Set user
     *
     * @param \Lci\BoilerBoxBundle\Entity\User $user
     * @return BonsAttachement
     */
    public function setUser(\Lci\BoilerBoxBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Lci\BoilerBoxBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }




    /**
     * Set enqueteNecessaire
     *
     * @param boolean $enqueteNecessaire
     * @return BonsAttachement
     */
    public function setEnqueteNecessaire($enqueteNecessaire)
    {
        $this->enqueteNecessaire = $enqueteNecessaire;

        return $this;
    }

    /**
     * Get enqueteNecessaire
     *
     * @return boolean 
     */
    public function getEnqueteNecessaire()
    {
        return $this->enqueteNecessaire;
    }

    /**
     * Set enqueteFaite
     *
     * @param boolean $enqueteFaite
     * @return BonsAttachement
     */
    public function setEnqueteFaite($enqueteFaite)
    {
        $this->enqueteFaite = $enqueteFaite;

        return $this;
    }

    /**
     * Get enqueteFaite
     *
     * @return boolean 
     */
    public function getEnqueteFaite()
    {
        return $this->enqueteFaite;
    }


    /**
     * Add fichiersPdf
     *
     * @param \Lci\BoilerBoxBundle\Entity\Fichier $fichiersPdf
     * @return BonsAttachement
     */
    public function addFichiersPdf(\Lci\BoilerBoxBundle\Entity\Fichier $fichiersPdf)
    {
        $this->fichiersPdf[] = $fichiersPdf;

		// On lie le fichier 
		$fichiersPdf->setBonAttachement($this);

        return $this;
    }

    /*
     * Remove fichiersPdf
     *
     * @param \Lci\BoilerBoxBundle\Entity\Fichier $fichiersPdf
    public function removeFichiersPdf(\Lci\BoilerBoxBundle\Entity\Fichier $fichiersPdf)
    {
        $this->fichiersPdf->removeElement($fichiersPdf);
		//$fichiersPdf->setBonAttachement(null);
    }
	*/


    /**
     * Get fichiersPdf
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFichiersPdf()
    {
        return $this->fichiersPdf;
    }


	/**
	 *
	 * @ORM\PrePersist
     * @ORM\PreUpdate
	*/
    public function setBonToFichiers(){
		foreach ($this->fichiersPdf as $fichier) {
			$fichier->setBonAttachement($this);
		}
	}

    /**
     * Set validationTechnique
     *
     * @param \Lci\BoilerBoxBundle\Entity\Validation $validationTechnique
     * @return BonsAttachement
     */
    public function setValidationTechnique(\Lci\BoilerBoxBundle\Entity\Validation $validationTechnique = null)
    {
        $this->validationTechnique = $validationTechnique;

        return $this;
    }

    /**
     * Get validationTechnique
     *
     * @return \Lci\BoilerBoxBundle\Entity\Validation 
     */
    public function getValidationTechnique()
    {
        return $this->validationTechnique;
    }

    /**
     * Set validationPiece
     *
     * @param \Lci\BoilerBoxBundle\Entity\Validation $validationPiece
     * @return BonsAttachement
     */
    public function setValidationPiece(\Lci\BoilerBoxBundle\Entity\Validation $validationPiece = null)
    {
        $this->validationPiece = $validationPiece;

        return $this;
    }

    /**
     * Get validationPiece
     *
     * @return \Lci\BoilerBoxBundle\Entity\Validation 
     */
    public function getValidationPiece()
    {
        return $this->validationPiece;
    }




    /**
     * Set validationPieceFaite
     *
     * @param \Lci\BoilerBoxBundle\Entity\Validation $validationPieceFaite
     * @return BonsAttachement
     */
    public function setValidationPieceFaite(\Lci\BoilerBoxBundle\Entity\Validation $validationPieceFaite = null)
    {
        $this->validationPieceFaite = $validationPieceFaite;

        return $this;
    }

    /**
     * Get validationPieceFaite
     *
     * @return \Lci\BoilerBoxBundle\Entity\Validation
     */
    public function getValidationPieceFaite()
    {
        return $this->validationPieceFaite;
    }


    /**
     * Set validationSAV
     *
     * @param \Lci\BoilerBoxBundle\Entity\Validation $validationSAV
     * @return BonsAttachement
     */
    public function setValidationSAV(\Lci\BoilerBoxBundle\Entity\Validation $validationSAV = null)
    {
        $this->validationSAV = $validationSAV;

        return $this;
    }

    /**
     * Get validationSAV
     *
     * @return \Lci\BoilerBoxBundle\Entity\Validation 
     */
    public function getValidationSAV()
    {
        return $this->validationSAV;
    }

    /**
     * Set validationFacturation
     *
     * @param \Lci\BoilerBoxBundle\Entity\Validation $validationFacturation
     * @return BonsAttachement
     */
    public function setValidationFacturation(\Lci\BoilerBoxBundle\Entity\Validation $validationFacturation = null)
    {
        $this->validationFacturation = $validationFacturation;

        return $this;
    }

    /**
     * Get validationFacturation
     *
     * @return \Lci\BoilerBoxBundle\Entity\Validation 
     */
    public function getValidationFacturation()
    {
        return $this->validationFacturation;
    }

    /**
     * Set nomDuClient
     *
     * @param string $nomDuContact
     * @return BonsAttachement
     */
    public function setNomDuContact($nomDuContact)
    {
        $this->nomDuContact = $nomDuContact;

        return $this;
    }

    /**
     * Get nomDuContact
     *
     * @return string 
     */
    public function getNomDuContact()
    {
        return $this->nomDuContact;
    }

    /**
     * Set dateInitialisation
     *
     * @param \DateTime $dateInitialisation
     * @return BonsAttachement
     */
    public function setDateInitialisation($dateInitialisation)
    {
        $this->dateInitialisation = $dateInitialisation;

        return $this;
    }

    /**
     * Get dateInitialisation
     *
     * @return \DateTime 
     */
    public function getDateInitialisation()
    {
        return $this->dateInitialisation;
    }

    /**
     * Set userInitiateur
     *
     * @param \Lci\BoilerBoxBundle\Entity\User $userInitiateur
     * @return BonsAttachement
     */
    public function setUserInitiateur(\Lci\BoilerBoxBundle\Entity\User $userInitiateur = null)
    {
        $this->userInitiateur = $userInitiateur;

        return $this;
    }

    /**
     * Get userInitiateur
     *
     * @return \Lci\BoilerBoxBundle\Entity\User 
     */
    public function getUserInitiateur()
    {
        return $this->userInitiateur;
    }

    /**
     * Set commentaires
     *
     * @param string $commentaires
     * @return BonsAttachement
     */
    public function setCommentaires($commentaires)
    {
        $this->commentaires = $commentaires;

        return $this;
    }

    /**
     * Get commentaires
     *
     * @return string 
     */
    public function getCommentaires()
    {
        return $this->commentaires;
    }



    /**
     * Set dateDebutIntervention
     *
     * @param \DateTime $dateDebutIntervention
     * @return BonsAttachement
     */
    public function setDateDebutIntervention($dateDebutIntervention)
    {
        $this->dateDebutIntervention = $dateDebutIntervention;

        return $this;
    }

    /**
     * Get dateDebutIntervention
     *
     * @return \DateTime 
     */
    public function getDateDebutIntervention()
    {
        return $this->dateDebutIntervention;
    }

    /**
     * Set dateFinIntervention
     *
     * @param \DateTime $dateFinIntervention
     * @return BonsAttachement
     */
    public function setDateFinIntervention($dateFinIntervention)
    {
        $this->dateFinIntervention = $dateFinIntervention;

        return $this;
    }

    /**
     * Get dateFinIntervention
     *
     * @return \DateTime 
     */
    public function getDateFinIntervention()
    {
        return $this->dateFinIntervention;
    }

    /**
     * Remove fichiersPdf
     *
     * @param \Lci\BoilerBoxBundle\Entity\Fichier $fichiersPdf
     */
    public function removeFichiersPdf(\Lci\BoilerBoxBundle\Entity\Fichier $fichiersPdf)
    {
        $this->fichiersPdf->removeElement($fichiersPdf);
    }

    /**
     * Set telephoneContactClient.
     *
     * @param string|null $telephoneContactClient
     *
     * @return BonsAttachement
     */
    public function setTelephoneContactClient($telephoneContactClient = null)
    {
        $this->telephoneContactClient = $telephoneContactClient;

        return $this;
    }

    /**
     * Get telephoneContactClient.
     *
     * @return string|null
     */
    public function getTelephoneContactClient()
    {
        return $this->telephoneContactClient;
    }

    /**
     * Set service.
     *
     * @param string $service
     *
     * @return BonsAttachement
     */
    public function setService($service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service.
     *
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set typeIntervention.
     *
     * @param string $typeIntervention
     *
     * @return BonsAttachement
     */
    public function setTypeIntervention($typeIntervention)
    {
        $this->typeIntervention = $typeIntervention;

        return $this;
    }

    /**
     * Get typeIntervention.
     *
     * @return string
     */
    public function getTypeIntervention()
    {
        return $this->typeIntervention;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return BonsAttachement
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * Set motif.
     *
     * @param string $motif
     *
     * @return BonsAttachement
     */
    public function setMotif($motif)
    {
        $this->motif= $motif;

        return $this;
    }

    /**
     * Get motif.
     *
     * @return string
     */
    public function getMotif()
    {
        return $this->motif;
    }



    /**
     * Add equipementBATicket.
     *
     * @param \Lci\BoilerBoxBundle\Entity\EquipementBATicket $equipementBATicket
     *
     * @return BonsAttachement
     */
    public function addEquipementBATicket(\Lci\BoilerBoxBundle\Entity\EquipementBATicket $equipementBATicket)
    {
        $this->equipementBATicket[] = $equipementBATicket;
		$equipementBATicket->addBonsAttachement($this);

        return $this;
    }

    /**
     * Remove equipementBaTicket.
     *
     * @param \Lci\BoilerBoxBundle\Entity\EquipementBATicket $equipementBATicket
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeEquipementBATicket(\Lci\BoilerBoxBundle\Entity\EquipementBATicket $equipementBATicket)
    {
		$equipementBATicket->removeBonsAttachement($this);
        return $this->equipementBATicket->removeElement($equipementBATicket);
    }

    /**
     * Get equipementBATicket.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEquipementBATicket()
    {
        return $this->equipementBATicket;
    }




    /**
     * Set cheminDossierPhoto.
     *
     * @param string $cheminDossierPhotos
     *
     * @return BonsAttachement
     */
    public function setCheminDossierPhotos($cheminDossierPhotos)
    {
        $this->cheminDossierPhotos = $cheminDossierPhotos;

        return $this;
    }

    /**
     * Get cheminDossierPhotos.
     *
     * @return string
     */
    public function getCheminDossierPhotos()
    {
        return $this->cheminDossierPhotos;
    }

}
