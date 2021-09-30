<?php
namespace Lci\BoilerBoxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * EtatParametre
 *
 * @ORM\Table(name="t_etat_parametre")
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Entity\EtatParametreRepository")
 */
class EtatParametre
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
     * @var \DateTime
     *
     * @ORM\Column(name="horodatage", type="datetime")
     */
    protected $horodatage;


    /**
     * @var integer
     *
     * @ORM\Column(name="etat", type="integer", nullable=true)
     *
    */
    protected $etat;


    /**
     * @ORM\ManyToOne(targetEntity="Lci\BoilerBoxBundle\Entity\Parametre", inversedBy="etatParametre", cascade={"persist"})
     * @ORM\JoinColumn(name="parametre_id", referencedColumnName="id", nullable=false)
    */
    protected $parametre;


    /**
     * @var string
     *
     * @ORM\Column(name="valeur", type="text")
     *
    */
    protected $valeur;


	public function __construct() 
	{
		$this->horodatage = new \Datetime();
	}





    /**
     * Set horodatage
     *
     * @param \DateTime $horodatage
     * @return Donnee
     */
    public function setHorodatage($horodatage)
    {
        $this->horodatage = $horodatage;

        return $this;
    }

    /**
     * Get horodatage
     *
     * @return \DateTime
     */
    public function getHorodatage()
    {
        return $this->horodatage;
    }


    /**
     * Set etat
     *
     * @param integer $etatCloud
     * @return EtatParametre
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * Get etat
     *
     * @return integer
     */
    public function getEtat()
    {
        return $this->etat;
    }




    /**
     * Set valeur
     *
     * @param string $valeur
     * @return Valeur
     */
    public function setValeur($valeur)
    {
        $this->valeur = $valeur;

        return $this;
    }

    /**
     * Get valeur
     *
     * @return string
     */
    public function getValeur()
    {
        return $this->valeur;
    }


    /**
     * Set parametre
     *
     * @param \Lci\BoilerBoxBundle\Entity\Parametre $parametre
     * @return EtatParametre
     */
    public function setParametre(\Lci\BoilerBoxBundle\Entity\Parametre $parametre = null)
    {
        $this->parametre = $parametre;
		$parametre->addEtatParametre($this);

        return $this;
    }

    /**
     * Get parametre
     *
     * @return \Lci\BoilerBoxBundle\Entity\Parametre
     */
    public function getParametre()
    {
        return $this->parametre;
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
}
