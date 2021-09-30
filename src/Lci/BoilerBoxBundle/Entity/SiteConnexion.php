<?php
//src/Lci/BoilerBoxBundle/Entity/SiteConnexion.php
// Fichier de validsation : # src/Lci/BoilerBoxBundle/Resources/config/validation.yml


namespace Lci\BoilerBoxBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * SiteConnexion
 * @ORM\Entity
 * @ORM\Table(name="site_connexion")
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Entity\SiteRepository")
 * @ORM\HasLifecycleCallbacks
*/
class SiteConnexion
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"groupSite"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string",length=255)
     * @Groups({"groupSite"})
     */ 
    protected $url;

    /**
     * @var string
     *
	 * Code pour l'accès au live : Correspond à la fin de l'url
     * @ORM\Column(type="string",length=255, nullable=true)
     * @Groups({"groupSite"})
     */
    protected $codeLive;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", name="disponibilite", options={"default":2})
     * @Groups({"groupSite"})
    */
    protected $disponibilite;

    /**
     * @var array
     * Indique que le site est accessible depuis boiler-box.fr ou/et ecatcher
     * @ORM\Column(type="array")
     * @Groups({"groupSite"})
    */
    protected $accesDistant;


    /**
     * @var array
     *
     * @ORM\Column(type="array")
     * @Groups({"groupSite"})
    */
    protected $typeConnexion;


	/**
	 * @var datetime
	 *
	 * @ORM\Column(type="datetime", nullable=true)
	*/
	protected $dateEchecConnexion;

	/**
	 * @var time
	 *
	 * @ORM\Column(type="time", nullable=true)
	*/
	protected $dureeEchecConnexionJournalier;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer")
	*/
	protected $nbEchecConnexionJournalier;


    /**
     * @var datetime
     *
     * @ORM\Column(type="datetime", nullable=true)
    */
    protected $dateEchecConnexionBB;


    /**
     * @var time
     *
     * @ORM\Column(type="time", nullable=true)
    */
    protected $dureeEchecConnexionJournalierBB;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
    */
    protected $nbEchecConnexionJournalierBB;





    /**
     *
     * @ORM\OneToMany(targetEntity="Lci\BoilerBoxBundle\Entity\Configuration", mappedBy="siteConnexion", cascade={"persist", "remove"})
	 * @Groups({"groupSite"})
     *
    */
    protected $configurations;

    /**
     *
     * @ORM\OneToOne(targetEntity="Lci\BoilerBoxBundle\Entity\Site", mappedBy="siteConnexion", cascade={"persist"})
    */
    protected $site;

    /**
     *
     * @ORM\Column(type="boolean")
     *
    */
    protected $surveillance;






    /**
     * Constructor
     */
    public function __construct()
    {
		$this->disponibilite = 2;
        $this->configurations = new \Doctrine\Common\Collections\ArrayCollection();
		$this->surveillance = false;
		$this->nbEchecConnexionJournalierBB = 0;
		$this->nbEchecConnexionJournalier = 0;
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
     * Set url
     *
     * @param string $url
     * @return Site
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }


    /**
     * Set disponibilite
     *
     * @param integer $disponibilite
     * @return Site
     */
    public function setDisponibilite($disponibilite)
    {
        $this->disponibilite = $disponibilite;

        return $this;
    }

    /**
     * Get disponibilite
     *
     * @return integer 
     */
    public function getDisponibilite()
    {
        return $this->disponibilite;
    }



    /**
     * Set accesDistant
     *
     * @param string $accesDistant
     * @return Site
     */
    public function setAccesDistant($accesDistant)
    {
        $this->accesDistant = $accesDistant;

        return $this;
    }

    /**
     * Get accesDistant
     *
     * @return boolean 
     */
    public function getAccesDistant()
    {
        return $this->accesDistant;
    }


    /**
     * Add configuration
     *
     * @param \Lci\BoilerBoxBundle\Entity\Configuration $configuration
     *
     * @return Site
     */
    public function addConfiguration(\Lci\BoilerBoxBundle\Entity\Configuration $configuration)
    {
        $this->configurations[] = $configuration;
        // On effectue la liaison inverse depuis le site.
        $configuration->setSite($this);
        return $this;
    }

    /**
     * Remove configuration
     *
     * @param \Lci\BoilerBoxBundle\Entity\Configuration $configuration
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeConfiguration(\Lci\BoilerBoxBundle\Entity\Configuration $configuration)
    {
        return $this->configurations->removeElement($configuration);
    }

    /**
     * Get configurations.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConfigurations()
    {
        return $this->configurations;
    }


    /**
     * Set nbDbDonnees.
     *
     * @param string $nbDbDonnees
     *
     * @return Site
     */
    public function setNbDbDonnees($nbDbDonnees)
    {
        $this->nbDbDonnees = $nbDbDonnees;

        return $this;
    }

    /**
     * Get nbDbDonnees.
     *
     * @return string
     */
    public function getNbDbDonnees()
    {
        return $this->nbDbDonnees;
    }

    /**
     * Set codeLive.
     *
     * @param string|null $codeLive
     *
     * @return Site
     */
    public function setCodeLive($codeLive = null)
    {
        $this->codeLive = $codeLive;

        return $this;
    }

    /**
     * Get codeLive.
     *
     * @return string|null
     */
    public function getCodeLive()
    {
        return $this->codeLive;
    }

    /**
     * Add user.
     *
     * @param \Lci\BoilerBoxBundle\Entity\User $user
     *
     * @return Site
     */
    public function addUser(\Lci\BoilerBoxBundle\Entity\User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user.
     *
     * @param \Lci\BoilerBoxBundle\Entity\User $user
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeUser(\Lci\BoilerBoxBundle\Entity\User $user)
    {
        return $this->users->removeElement($user);
    }

    /**
     * Get users.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set typeConnexion.
     *
     * @param array $typeConnexion
     *
     * @return Site
     */
    public function setTypeConnexion($typeConnexion)
    {
        $this->typeConnexion = $typeConnexion;

        return $this;
    }

    /**
     * Get typeConnexion.
     *
     * @return array
     */
    public function getTypeConnexion()
    {
        return $this->typeConnexion;
    }

    /**
     * Set site.
     *
     * @param \Lci\BoilerBoxBundle\Entity\Site|null $site
     *
     * @return SiteConnexion
     */
    public function setSite(\Lci\BoilerBoxBundle\Entity\Site $site = null)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site.
     *
     * @return \Lci\BoilerBoxBundle\Entity\Site|null
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Set surveillance.
     *
     * @param bool $surveillance
     *
     * @return SiteConnexion
     */
    public function setSurveillance($surveillance)
    {
        $this->surveillance = $surveillance;

        return $this;
    }

    /**
     * Get surveillance.
     *
     * @return bool
     */
    public function getSurveillance()
    {
        return $this->surveillance;
    }

    /**
     * Set dateEchecConnexion.
     *
     * @param \DateTime $dateEchecConnexion
     *
     * @return SiteConnexion
     */
    public function setDateEchecConnexion($dateEchecConnexion)
    {
        $this->dateEchecConnexion = $dateEchecConnexion;

        return $this;
    }

    /**
     * Get dateEchecConnexion.
     *
     * @return \DateTime
     */
    public function getDateEchecConnexion()
    {
        return $this->dateEchecConnexion;
    }

    /**
     * Set dureeEchecConnexionJournalier.
     *
     * @param \DateTime $dureeEchecConnexionJournalier
     *
     * @return SiteConnexion
     */
    public function setDureeEchecConnexionJournalier($dureeEchecConnexionJournalier)
    {
        $this->dureeEchecConnexionJournalier = $dureeEchecConnexionJournalier;

        return $this;
    }

    /**
     * Get dureeEchecConnexionJournalier.
     *
     * @return \DateTime
     */
    public function getDureeEchecConnexionJournalier()
    {
        return $this->dureeEchecConnexionJournalier;
    }

    /**
     * Set nbEchecConnexionJournalier.
     *
     * @param int $nbEchecConnexionJournalier
     *
     * @return SiteConnexion
     */
    public function setNbEchecConnexionJournalier($nbEchecConnexionJournalier)
    {
        $this->nbEchecConnexionJournalier = $nbEchecConnexionJournalier;

        return $this;
    }

    /**
     * Get nbEchecConnexionJournalier.
     *
     * @return int
     */
    public function getNbEchecConnexionJournalier()
    {
        return $this->nbEchecConnexionJournalier;
    }

    /**
     * Set dateEchecConnexionBB.
     *
     * @param \DateTime $dateEchecConnexionBB
     *
     * @return SiteConnexion
     */
    public function setDateEchecConnexionBB($dateEchecConnexionBB)
    {
        $this->dateEchecConnexionBB = $dateEchecConnexionBB;

        return $this;
    }

    /**
     * Get dateEchecConnexionBB.
     *
     * @return \DateTime
     */
    public function getDateEchecConnexionBB()
    {
        return $this->dateEchecConnexionBB;
    }

    /**
     * Set dureeEchecConnexionJournalierBB.
     *
     * @param \DateTime $dureeEchecConnexionJournalierBB
     *
     * @return SiteConnexion
     */
    public function setDureeEchecConnexionJournalierBB($dureeEchecConnexionJournalierBB)
    {
        $this->dureeEchecConnexionJournalierBB = $dureeEchecConnexionJournalierBB;

        return $this;
    }

    /**
     * Get dureeEchecConnexionJournalierBB.
     *
     * @return \DateTime
     */
    public function getDureeEchecConnexionJournalierBB()
    {
        return $this->dureeEchecConnexionJournalierBB;
    }

    /**
     * Set nbEchecConnexionJournalierBB.
     *
     * @param int $nbEchecConnexionJournalierBB
     *
     * @return SiteConnexion
     */
    public function setNbEchecConnexionJournalierBB($nbEchecConnexionJournalierBB)
    {
        $this->nbEchecConnexionJournalierBB = $nbEchecConnexionJournalierBB;

        return $this;
    }

    /**
     * Get nbEchecConnexionJournalierBB.
     *
     * @return int
     */
    public function getNbEchecConnexionJournalierBB()
    {
        return $this->nbEchecConnexionJournalierBB;
    }
}
