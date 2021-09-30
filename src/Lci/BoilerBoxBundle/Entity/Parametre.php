<?php
namespace Lci\BoilerBoxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Parametre
 *
 * @ORM\Table(name="t_parametre",
 * uniqueConstraints={@ORM\UniqueConstraint(name="UK_search", columns={"designation"})})
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Entity\ParametreRepository")
 */
class Parametre
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @var string
     *
     * @ORM\Column(name="designation", type="string", length=255)
	*/
    protected $designation;


    /**
     * @ORM\OneToMany(targetEntity="Lci\BoilerBoxBundle\Entity\EtatParametre", mappedBy="parametre")
    */
    protected $etatParametre;


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
     * Set designation
     *
     * @param string $designation
     * @return Parametre
     */
    public function setDesignation($designation)
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * Get designation
     *
     * @return string
     */
    public function getDesignation()
    {
        return $this->designation;
    }

    /**
     * Add etatParametre
     *
     * @param \Lci\BoilerBoxBundle\Entity\EtatParametre $etatParametre
     * @return Parametre
     */
    public function addEtatParametre(\Lci\BoilerBoxBundle\Entity\EtatParametre $etatParametre)
    {
        $this->etatParametre[] = $etatParametre;

        return $this;
    }

    /**
     * Remove etatParametre
     *
     * @param \Lci\BoilerBoxBundle\Entity\EtatParametre $etatParametre
     */
    public function removeEtatParametre(\Lci\BoilerBoxBundle\Entity\EtatParametre $etatParametre)
    {
        $this->etatParametre->removeElement($etatParametre);
    }

    /**
     * Get etatParametre
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEtatParametre()
    {
        return $this->etatParametre;
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->etatParametre = new \Doctrine\Common\Collections\ArrayCollection();
    }
}
