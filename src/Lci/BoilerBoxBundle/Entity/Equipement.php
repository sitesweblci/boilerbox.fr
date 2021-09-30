<?php
//src/Lci/BoierBoxBundle/Entity/Equipement.php

namespace Lci\BoilerBoxBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\ORM\Mapping as ORM;

/**
 * Equipement
 *
 * @ORM\Entity
 * @UniqueEntity("type")
 * @ORM\Table(name="equipement", uniqueConstraints={@UniqueConstraint(name="cst_type", columns={"type"})})
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Entity\EquipementRepository")
*/
class Equipement
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;

    /**
     * @var string
     * @Assert\NotBlank()
     *
     * @ORM\Column(type="string", length=255, nullable=false)
    */
    protected $type;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false, options={"default":1})
    */
    protected $actif;

    /**
     * One Equipement can have many problems
     * @ORM\OneToMany(targetEntity="ProblemeTechnique", mappedBy="equipement")
    */
    protected $problemeTechnique;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->problemeTechnique = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set type
     *
     * @param string $type
     * @return Equipement
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
     * Add problemeTechnique
     *
     * @param \Lci\BoilerBoxBundle\Entity\ProblemeTechnique $problemeTechnique
     * @return Equipement
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
     * Set actif
     *
     * @param boolean $actif
     * @return Equipement
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
}
