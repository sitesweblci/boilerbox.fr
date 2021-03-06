<?php
namespace Ipc\ProgBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * EtatParametre
 *
 * @ORM\Table(name="t_etat_parametre")
 * @ORM\Entity(repositoryClass="Ipc\ProgBundle\Entity\EtatParametreRepository")
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
     * @ORM\Column(name="etat_ipc", type="integer")
     *
    */
    protected $etatIpc;


    /**
     * @var integer
     *
     * @ORM\Column(name="etat_cloud", type="integer", nullable=true)
     *
    */
    protected $etatCloud;


    /**
     * @ORM\ManyToOne(targetEntity="Ipc\ProgBundle\Entity\Parametre", inversedBy="etatParametre", cascade={"persist"})
     * @ORM\JoinColumn(name="parametre_id", referencedColumnName="id", nullable=false)
    */
    protected $parametre;


    /**
     * @ORM\ManyToOne(targetEntity="Ipc\ProgBundle\Entity\Localisation", inversedBy="etatParametre", cascade={"persist"})
     * @ORM\JoinColumn(name="localisation_id", referencedColumnName="id", nullable=true)
    */
    protected $localisation;



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
     * Set etatIpc
     *
     * @param integer $etatIpc
     * @return EtatParametre
     */
    public function setEtatIpc($etatIpc)
    {
        $this->etatIpc = $etatIpc;

        return $this;
    }

    /**
     * Get etatIpc
     *
     * @return integer
     */
    public function getEtatIpc()
    {
        return $this->etatIpc;
    }

    /**
     * Set etatCloud
     *
     * @param integer $etatCloud
     * @return EtatParametre
     */
    public function setEtatCloud($etatCloud)
    {
        $this->etatCloud = $etatCloud;

        return $this;
    }

    /**
     * Get etatCloud
     *
     * @return integer
     */
    public function getEtatCloud()
    {
        return $this->etatCloud;
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
     * @param \Ipc\ProgBundle\Entity\Parametre $parametre
     * @return EtatParametre
     */
    public function setParametre(\Ipc\ProgBundle\Entity\Parametre $parametre = null)
    {
        $this->parametre = $parametre;
		$parametre->addEtatParametre($this);

        return $this;
    }

    /**
     * Get parametre
     *
     * @return \Ipc\ProgBundle\Entity\Parametre
     */
    public function getParametre()
    {
        return $this->parametre;
    }


    /**
     * Set localisation
     *
     * @param \Ipc\ProgBundle\Entity\Localisation $localisation
     * @return EtatParametre
     */
    public function setLocalisation(\Ipc\ProgBundle\Entity\Localisation $localisation = null)
    {
        $this->localisation = $localisation;
		$localisation->addEtatParametre($this);

        return $this;
    }

    /**
     * Get localisation
     *
     * @return \Ipc\ProgBundle\Entity\Localisation
     */
    public function getLocalisation()
    {
        return $this->localisation;
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
