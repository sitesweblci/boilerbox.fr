<?php 
// src/Lci/BoilerBoxBundle/Entity/ObjRechercheBonsAttachement.php
namespace Lci\BoilerBoxBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * ObjRechercheBonsAttachement.php
 *
*/

class ObjRechercheBonsAttachement {
	/**
	 * @var string
	 *
	 * @Assert\Type("string")
	 * @Assert\Length(min=6, max=6, exactMessage= "Le numéro doit contenir 6 chiffres")
	 * @Assert\Regex("/^\d{6}$/", message="Format incorrect. 6 chiffres attendus")
	*/
	protected $numeroBA;

	/**
	 * @var string
	 *
     * @Assert\Type("string")
     * @Assert\Length(min=4, max=7, minMessage="Format incorrect. Nombre de caractères minimum = 4", maxMessage="Format incorrect. Nombre de caractères maximum = 7")
     * 
    */
    protected $numeroAffaire;


	/**
     * @var date
	 *
	 * @Assert\Date(message="Format incorrect. Format jj/mm/AAAA attendu.")
	 *
	*/
	protected $dateSignature;


	/**
	 * Plusieurs bons d'attachements peuvent cibler un site
	 *
	*/
	protected $site;


    /**
     * Plusieurs bons d'attachements peuvent cibler un utilisateur
     *
    */
    protected $user;

	/**
     * Plusieurs bons d'attachements peuvent cibler un initiateur
     *
    */
    protected $userInitiateur;


   /**
     * @var string
     *
     * @Assert\Type("string")
    */
    protected $nomDuContact;


    // Plage de date utilisée pour affiner la recherche de bons
    /**
     * @var date
     *
     * @Assert\Date(message="Format attendu jj/mm/AAAA")
     *
    */
    protected $dateMin;


	/**
	 * @var date
     * 
     * @Assert\Date(message="format attendu : jj/mm/AAAA");
    */
	protected $dateMax;



    // Plage de date utilisée pour affiner la recherche de bons
    /**
     * @var date
     *
     * @Assert\Date(message="Format attendu jj/mm/AAAA")
     *
    */
    protected $dateMinInitialisation;


    /**
     * @var date
     *
     * @Assert\Date(message="format attendu : jj/mm/AAAA");
    */
    protected $dateMaxInitialisation;


    // Plage de date utilisée pour affiner la recherche de bons
    /**
     * @var date
     *
     * @Assert\Date(message="Format attendu jj/mm/AAAA")
     *
    */
    protected $dateMinIntervention;


    /**
     * @var date
     *
     * @Assert\Date(message="format attendu : jj/mm/AAAA");
    */
    protected $dateMaxIntervention;



	/**
	 * @var boolean
	 *
	*/
	protected $saisie;


    /**
     * Personne qui valide le ticket
     *
    */
    protected $valideur;


    /**
     * @var boolean
     *
    */
    protected $validationTechnique;

    /**
     * @var boolean
     *
    */
    protected $validationHoraire;

    /**
     * @var boolean
     *
    */
    protected $validationSAV;

    /**
     * @var boolean
     *
    */
    protected $validationFacturation;



	/**
	 * @var boolean
	 *
	*/
	protected $sensValidation;




    /**
     * Set numeroAffaire
     *
     * @param string $numeroAffaire
     * @return ObjRechercheBonsAttachement
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
     * Set numeroBA
     *
     * @param string $numeroBA
     * @return ObjRechercheBonsAttachement
    */
    public function setNumeroBA($numeroBA)
    {
        $this->numeroBA = strtoupper($numeroBA);

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
     * Set nomDuContact
     *
     * @param string $nomDuContact
     * @return ObjRechercheBonsAttachement
    */
    public function setNomDuContact($nomDuContact)
    {
        $this->nomDuContact = ucfirst(strtolower($nomDuContact));

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
     * Set dateSignature
     *
     * @param \DateTime $dateSignature
     * @return ObjRechercheBonsAttachement
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
     * Set site
     *
     * @param \Lci\BoilerBoxBundle\Entity\SiteBA $site
     * @return ObjRechercheBonsAttachement
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
     * @return ObjRechercheBonsAttachement
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
     * Set userInitiateur
     *
     * @param \Lci\BoilerBoxBundle\Entity\User $userInitiateur
     * @return ObjRechercheBonsAttachement
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


	// Partie utilisée pour affiner les recherches des bons *********************************

    /**
     * Set dateMin
     *
     * @param \DateTime $dateMin
     * @return ObjRechercheBonsAttachement
     */
    public function setDateMin($dateMin)
    {
        $this->dateMin = $dateMin;

        return $this;
    }

    /**
     * Get dateMin
     *
     * @return \DateTime
     */
    public function getDateMin()
    {
        return $this->dateMin;
    }


    /**
     * Set dateMax
     *
     * @param \DateTime $dateMax
     * @return ObjRechercheBonsAttachement
     */
    public function setDateMax($dateMax)
    {
        $this->dateMax = $dateMax;

        return $this;
    }

    /**
     * Get dateMax
     *
     * @return \DateTime
     */
    public function getDateMax()
    {
        return $this->dateMax;
    }



	/********************************* DATES D'INITIALISATION DU BON ********************************/
    /**
     * Set dateMinInitialisation
     *
     * @param \DateTime $dateMin
     * @return ObjRechercheBonsAttachement
     */
    public function setDateMinInitialisation($dateMinInitialisation)
    {
        $this->dateMinInitialisation = $dateMinInitialisation;

        return $this;
    }

    /**
     * Get dateMinInitialisation
     *
     * @return \DateTime
     */
    public function getDateMinInitialisation()
    {
        return $this->dateMinInitialisation;
    }


    /**
     * Set dateMaxInitialisation
     *
     * @param \DateTime $dateMaxInitialisation
     * @return ObjRechercheBonsAttachement
     */
    public function setDateMaxInitialisation($dateMaxInitialisation)
    {
        $this->dateMaxInitialisation = $dateMaxInitialisation;

        return $this;
    }

    /**
     * Get dateMaxInitialisation
     *
     * @return \DateTime
     */
    public function getDateMaxInitialisation()
    {
        return $this->dateMaxInitialisation;
    }


    /**
     * Set dateMinIntervention
     *
     * @param \DateTime $dateMin
     * @return ObjRechercheBonsAttachement
     */
    public function setDateMinIntervention($dateMinIntervention)
    {
        $this->dateMinIntervention = $dateMinIntervention;

        return $this;
    }

    /**
     * Get dateMinIntervention
     *
     * @return \DateTime
     */
    public function getDateMinIntervention()
    {
        return $this->dateMinIntervention;
    }


    /**
     * Set dateMaxIntervention
     *
     * @param \DateTime $dateMaxIntervention
     * @return ObjRechercheBonsAttachement
     */
    public function setDateMaxIntervention($dateMaxIntervention)
    {
        $this->dateMaxIntervention = $dateMaxIntervention;

        return $this;
    }

    /**
     * Get dateMaxIntervention
     *
     * @return \DateTime
     */
    public function getDateMaxIntervention()
    {
        return $this->dateMaxIntervention;
    }


	// Return -1 si la date min est > à la date max
	public function compareIntervalleDate() {
		return ($this->dateMin > $this->dateMax)?-1:0;
	}

    /**
     * Set valideur
     *
     * @param \Lci\BoilerBoxBundle\Entity\User $valideur
     * @return ObjRechercheBonsAttachement
     */
    public function setValideur(\Lci\BoilerBoxBundle\Entity\User $valideur = null)
    {
        $this->valideur = $valideur;

        return $this;
    }

    /**
     * Get valideur
     *
     * @return \Lci\BoilerBoxBundle\Entity\User
     */
    public function getValideur()
    {
        return $this->valideur;
    }

	/**
	 * Get ValidationTechnique
	 *
	 * @return boolean
	*/
	public function getValidationTechnique() {
		return $this->validationTechnique;
	}


	/**
	 * Set ValidationTechnique
	 *
	 * param boolean $validation
	 * @return ObjRechercheBonsAttachement
	*/
	public function setValidationTechnique($validation) {
		$this->validationTechnique = $validation;
		return $this;
	}

    /**
     * Get ValidationHoraire
     *
     * @return boolean
    */
    public function getValidationHoraire() {
        return $this->validationHoraire;
    }

    /**
     * Set ValidationHoraire
     *
     * param boolean $validation
     * @return ObjRechercheBonsAttachement
    */
    public function setValidationHoraire($validation) {
        $this->validationHoraire = $validation;
        return $this;
    }


    /**
     * Get ValidationSAV
     *
     * @return boolean
    */
    public function getValidationSAV() {
        return $this->validationSAV;
    }

    /**
     * Set ValidationSAV
     *
     * param boolean $validation
     * @return ObjRechercheBonsAttachement
    */
    public function setValidationSAV($validation) {
        $this->validationSAV = $validation;
        return $this;
    }


    /**
     * Get ValidationFacturation
     *
     * @return boolean
    */
    public function getValidationFacturation() {
        return $this->validationFacturation;
    }

    /**
     * Set ValidationFacturation
     *
     * param boolean $validation
     * @return ObjRechercheBonsAttachement
    */
    public function setValidationFacturation($validation) {
        $this->validationFacturation = $validation;
        return $this;
    }



	/**
	 * Set Saisie
	 *
	 * param boolean $saisie
	 * @return ObjRechercheBonAttachement
	*/
	public function setSaisie($saisie) {
		$this->saisie = $saisie;
		return $this;
	}


	/**
	 * Get Saisie
	 * 
	 * @return boolean
	*/
	public function getSaisie() {
		return $this->saisie;
	}



    /**
     * Get SensValidation
     *
     * @return boolean
    */
    public function getSensValidation() {
        return $this->sensValidation;
    }

    /**
     * Set SensValidation
     *
     * param boolean $sensValidation
     * @return ObjRechercheBonsAttachement
    */
    public function setSensValidation($sensValidation) {
        $this->sensValidation = $sensValidation;
        return $this;
    }




}
