<?php
//src/Lci/BoierBoxBundle/Entity/Module.php

namespace Lci\BoilerBoxBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\ORM\Mapping as ORM;


/**
 * Module
 *
 * @ORM\Entity
 * @UniqueEntity("numero")
 * @ORM\Table(name="module", uniqueConstraints={@UniqueConstraint(name="cst_numero", columns={"numero"})})
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Entity\ModuleRepository")
*/
class Module
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer
     * @Assert\NotBlank()
     *
     * @ORM\Column(type="integer", nullable=false, options={"unsigned":true})
     * @ORM\OrderBy({"numero" = "ASC"})
     * @Assert\NotBlank(message="Veuillez indiquer un numéro valide") 
    */
    protected $numero;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Veuillez indiquer le type du module")
    */
    protected $type;

    /**
     * One Module can have many problems
     * @ORM\ManyToMany(targetEntity="ProblemeTechnique", mappedBy="module")
    */
    protected $problemeTechnique;

    /**
     * Many Modules can be send to a site
     * @ORM\ManyToOne(targetEntity="Site", cascade={"persist"}, inversedBy="module")
    */
    protected $site;

	/*
 	 * One probleme can have many files
	 * @ORM\OneToMany(targetEntity="FichierJoint", mappedBy="problemeTechnique")
    *
	protected $fichiersJoint;
	*/

    /**
     * @var string
     * @Assert\NotBlank(message="Veuillez indiquer le nom du module")
     *  
     * @ORM\Column(type="string")
    */
    protected $nom;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", options={"default":0})
    */
    protected $present;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false, options={"default":1})
    */
    protected $actif;

    /**
     * @var date
     * @Assert\NotBlank()
     * @Assert\Date()
     *
     * @ORM\Column(type="date", name="date_mouvement", nullable=false)
    */
    protected $dateMouvement;



    /**
     * Constructor
     */
    public function __construct()
    {
        $this->problemeTechnique = new \Doctrine\Common\Collections\ArrayCollection();
		$this->dateMouvement = new \Datetime();
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
     * Set numero
     *
     * @param string $numero
     * @return Module
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * Get numero
     *
     * @return string 
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Add problemeTechnique
     *
     * @param \Lci\BoilerBoxBundle\Entity\ProblemeTechnique $problemeTechnique
     * @return Module
     */
    public function addProblemeTechnique(\Lci\BoilerBoxBundle\Entity\ProblemeTechnique $problemeTechnique)
    {
        $this->problemeTechnique[] = $problemeTechnique;

        return $this;
    }

    /**
     * Remove problemeTechnique
     *
     * @param \Lci\BoilerBoxBundle\Entity\ProblemeTechnique $problemeTechnique
     */
    public function removeProblemeTechnique(\Lci\BoilerBoxBundle\Entity\ProblemeTechnique $problemeTechnique)
    {
        $this->problemeTechnique->removeElement($problemeTechnique);
    }

    /**
     * Get problemeTechnique
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProblemeTechnique()
    {
        return $this->problemeTechnique;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Module
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set present
     *
     * @param boolean $present
     * @return Module
     */
    public function setPresent($present)
    {
        $this->present = $present;

        return $this;
    }

    /**
     * Get present
     *
     * @return boolean 
     */
    public function getPresent()
    {
        return $this->present;
    }

    /**
     * Set dateMouvement
     *
     * @param \DateTime $dateMouvement
     * @return Module
     */
    public function setDateMouvement($dateMouvement)
    {
        $this->dateMouvement = $dateMouvement;

        return $this;
    }

    /**
     * Get dateMouvement
     *
     * @return \DateTime 
     */
    public function getDateMouvement()
    {
        return $this->dateMouvement;
    }

	/*
	 * Fonction utilisée pour l'affichage dans les select
	*/
	public function getInfoSelect(){
      		return $this->numero." (".$this->nom.")";
      	}

    /*
     * Add fichiersJoint
     *
     * @param \Lci\BoilerBoxBundle\Entity\FichierJoint $fichiersJoint
     * @return Module
     *
    public function addFichiersJoint(\Lci\BoilerBoxBundle\Entity\FichierJoint $fichiersJoint)
    {
        $this->fichiersJoint[] = $fichiersJoint;

        return $this;
    }
	*/

    /*
     * Remove fichiersJoint
     *
     * @param \Lci\BoilerBoxBundle\Entity\FichierJoint $fichiersJoint
     *
    public function removeFichiersJoint(\Lci\BoilerBoxBundle\Entity\FichierJoint $fichiersJoint)
    {
        $this->fichiersJoint->removeElement($fichiersJoint);
    }
	*/

    /*
     * Get fichiersJoint
     *
     * @return \Doctrine\Common\Collections\Collection 
     *
    public function getFichiersJoint()
    {
        return $this->fichiersJoint;
    }
	*/

    /**
     * Set actif
     *
     * @param boolean $actif
     * @return Module
     */
    public function setActif($actif)
    {
        $this->actif = $actif;

        return $this;
    }

    /**
     * Get actif
     *
     * @return boolean 
     */
    public function getActif()
    {
        return $this->actif;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Module
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set site
     *
     * @param \Lci\BoilerBoxBundle\Entity\Site $site
     * @return Module
     */
    public function setSite(\Lci\BoilerBoxBundle\Entity\Site $site = null)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site
     *
     * @return \Lci\BoilerBoxBundle\Entity\Site 
     */
    public function getSite()
    {
        return $this->site;
    }
}
