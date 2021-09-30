<?php 
// src/Lci/BoilerBoxBundle/Entity/Validation.php
namespace Lci\BoilerBoxBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Validation
 *
 * @ORM\Entity
 * @ORM\Table(name="validation")
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Entity\ValidationRepository")
 * @ORM\HasLifecycleCallbacks()
*/

class Validation {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var boolean
     *
     * @Assert\Type("bool")
     *
     * @ORM\Column(type="boolean", options={"default"=0})
    */
    protected $valide;


	/**
     * @var date
	 *
	 * @Assert\Date(message="Format incorrect. Format jj/mm/AAAA attendu.")
	 * @Assert\NotBlank()
	 *
	 * @ORM\Column(type="date", name="date_de_validation")
	*/
	protected $dateDeValidation;


	/**
	 * @var string
	 *
	 * @ORM\Column(type="text")
	*/
	protected $type;

    /**
     * Un utilisateur valide le bon
     *
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="validations", cascade={"persist"})
     * @ORM\OrderBy({"label" = "ASC"})
    */
    protected $user;



	public function __construct() {
		$this->dateDeValidation = new \Datetime();
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
     * Set valide
     *
     * @param boolean $valide
     * @return Validation
     */
    public function setValide($valide)
    {
        $this->valide = $valide;

        return $this;
    }

    /**
     * Get valide
     *
     * @return boolean 
     */
    public function getValide()
    {
        return $this->valide;
    }

    /**
     * Set dateDeValidation
     *
     * @param \DateTime $dateDeValidation
     * @return Validation
     */
    public function setDateDeValidation($dateDeValidation)
    {
        $this->dateDeValidation = $dateDeValidation;

        return $this;
    }

    /**
     * Get dateDeValidation
     *
     * @return \DateTime 
     */
    public function getDateDeValidation()
    {
        return $this->dateDeValidation;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Validation
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
     * Set user
     *
     * @param \Lci\BoilerBoxBundle\Entity\User $user
     * @return Validation
     */
    public function setUser(\Lci\BoilerBoxBundle\Entity\User $user = null)
    {
        $this->user = $user;
		// Relation inverse
		$this->user->addValidation($this);
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
}
