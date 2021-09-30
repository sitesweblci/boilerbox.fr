<?php
//src/Lci/BoilerBoxBundle/Entity/ProblemeTechnique.php

namespace Lci\BoilerBoxBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * ProblemeTechnique
 *
 * @ORM\Entity
 * @ORM\Table(name="probleme_technique")
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Entity\ProblemeTechniqueRepository")
*/

class ProblemeTechnique
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Many problems can reference one module
     * @ORM\ManyToMany(targetEntity="Module", cascade={"persist"}, inversedBy="problemeTechnique")
    */
    protected $module;

    /** 
     * Many problems can reference one equipement
     * @ORM\ManyToOne(targetEntity="Equipement", cascade={"persist"}, inversedBy="problemeTechnique")
     * @ORM\JoinColumn(nullable=false)
    */
    protected $equipement;

    /**
     * Many problems can reference one user
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"}, inversedBy="problemeTechnique")
    */
    protected $user;

    /**
     * One probleme can have many files
     * @ORM\ManyToMany(targetEntity="FichierJoint", mappedBy="problemeTechnique", cascade={"persist"})
    */
    protected $fichiersJoint;

    /**
     * @var date
     * @Assert\NotBlank()
     * @Assert\Date()
     *
     * @ORM\Column(type="date", name="date_signalement", nullable=false)
    */
    protected $dateSignalement;
	
    /**
     * @var text
     * @Assert\NotBlank(message="Veuillez remplir la description du problème svp.")
     *
     * @ORM\Column(type="text", nullable=false)
    */
    protected $description;

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
    */
    protected $solution;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false)
    */
    protected $corrige;

    /**
     * @var date
     * @Assert\Date()
     *
     * @ORM\Column(type="date", name="date_correction", nullable=true)
    */
    protected $dateCorrection;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
    */
    protected $cloture;

	/**
     * @var date
     * @Assert\Date()
     *
     * @ORM\Column(type="date", name="date_cloture", nullable=true)
    */
    protected $dateCloture;


    /**
     * var boolean
     *
     * @ORM\Column(type="boolean")
    */
    protected $bloquant;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->module = new \Doctrine\Common\Collections\ArrayCollection();
		$this->fichiersJoint = new \Doctrine\Common\Collections\ArrayCollection();
		$this->dateSignalement = new \Datetime();
		$this->dateCorrection = new \Datetime();
		$this->dateCloture = new \Datetime();
		$this->corrige = false;
		$this->cloture = false;
		$this->bloquant = false;
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
     * @param integer
     * @return ProblemeTechnique
    */
    public function setId($id)
    {
		$this->id = $id;

        return $this;
    }


    /**
     * Set dateSignalement
     *
     * @param \DateTime $dateSignalement
     * @return ProblemeTechnique
     */
    public function setDateSignalement($dateSignalement)
    {
        $this->dateSignalement = $dateSignalement;

        return $this;
    }

    /**
     * Get dateSignalement
     *
     * @return \DateTime 
     */
    public function getDateSignalement()
    {
        return $this->dateSignalement;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return ProblemeTechnique
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set solution
     *
     * @param string $solution
     * @return ProblemeTechnique
     */
    public function setSolution($solution)
    {
        $this->solution = $solution;

        return $this;
    }

    /**
     * Get solution
     *
     * @return string 
     */
    public function getSolution()
    {
        return $this->solution;
    }

    /**
     * Set corrige
     *
     * @param boolean $corrige
     * @return ProblemeTechnique
     */
    public function setCorrige($corrige)
    {
        $this->corrige = $corrige;

        return $this;
    }

    /**
     * Get corrige
     *
     * @return boolean 
     */
    public function getCorrige()
    {
        return $this->corrige;
    }

    /**
     * Set cloture
     *
     * @param boolean $cloture
     * @return ProblemeTechnique
     */
    public function setCloture($cloture)
    {
        $this->cloture = $cloture;

        return $this;
    }

    /**
     * Get cloture
     *
     * @return boolean 
     */
    public function getCloture()
    {
        return $this->cloture;
    }


    /**
     * Set dateCorrection
     *
     * @param \DateTime $dateCorrection
     * @return ProblemeTechnique
     */
    public function setDateCorrection($dateCorrection)
    {
        $this->dateCorrection = $dateCorrection;

        return $this;
    }

    /**
     * Get dateCorrection
     *
     * @return \DateTime 
     */
    public function getDateCorrection()
    {
        return $this->dateCorrection;
    }

    /**
     * Set equipement
     *
     * @param \Lci\BoilerBoxBundle\Entity\Equipement $equipement
     * @return ProblemeTechnique
     */
    public function setEquipement(\Lci\BoilerBoxBundle\Entity\Equipement $equipement = null)
    {
        $this->equipement = $equipement;

        return $this;
    }

    /**
     * Get equipement
     *
     * @return \Lci\BoilerBoxBundle\Entity\Equipement 
     */
    public function getEquipement()
    {
        return $this->equipement;
    }

    /**
     * Set user
     *
     * @param \Lci\BoilerBoxBundle\Entity\User $user
     * @return ProblemeTechnique
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
     * Add module
     *
     * @param \Lci\BoilerBoxBundle\Entity\Module $module
     * @return ProblemeTechnique
     */
    public function addModule(\Lci\BoilerBoxBundle\Entity\Module $module)
    {
        $this->module[] = $module;

        return $this;
    }

    /**
     * Remove module
     *
     * @param \Lci\BoilerBoxBundle\Entity\Module $module
     */
    public function removeModule(\Lci\BoilerBoxBundle\Entity\Module $module)
    {
        $this->module->removeElement($module);
    }

    /**
     * Get module
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set bloquant
     *
     * @param boolean $bloquant
     * @return ProblemeTechnique
     */
    public function setBloquant($bloquant)
    {
        $this->bloquant = $bloquant;

        return $this;
    }

    /**
     * Get bloquant
     *
     * @return boolean 
     */
    public function getBloquant()
    {
        return $this->bloquant;
    }

    /**
     * Add fichiersJoint
     *
     * @param \Lci\BoilerBoxBundle\Entity\FichierJoint $fichiersJoint
     * @return ProblemeTechnique
     */
    public function addFichiersJoint(\Lci\BoilerBoxBundle\Entity\FichierJoint $fichiersJoint)
    {
        $this->fichiersJoint[] = $fichiersJoint;
        return $this;
    }

	public function addReverseFichierJoint(\Lci\BoilerBoxBundle\Entity\FichierJoint $fichierJoint){
      		$fichierJoint->addProblemeTechnique($this);
      		return $this;
      	}

    /**
     * Remove fichiersJoint
     *
     * @param \Lci\BoilerBoxBundle\Entity\FichierJoint $fichiersJoint
     */
    public function removeFichiersJoint(\Lci\BoilerBoxBundle\Entity\FichierJoint $fichiersJoint)
    {
        $this->fichiersJoint->removeElement($fichiersJoint);
    }

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
     * Set dateCloture
     *
     * @param \DateTime $dateCloture
     * @return ProblemeTechnique
     */
    public function setDateCloture($dateCloture)
    {
        $this->dateCloture = $dateCloture;

        return $this;
    }

    /**
     * Get dateCloture
     *
     * @return \DateTime 
     */
    public function getDateCloture()
    {
        return $this->dateCloture;
    }

  /**
   * @Assert\Callback
   */
  public function isContentValid(ExecutionContext $context)
  {
	if (count($this->getModule()) == 0){
      		/*
      		$context->buildViolation('This name sounds totally fake!')
                      ->atPath('firstName')
                      ->addViolation();
      		*/
      		$context->addViolation('Veuillez sélectionner au moins un module');
      	}
  }


	/* Réinitialisation de la liste des modules associés */
   	public function clearModule() {
		$this->getModule()->clear();
  	}


}
