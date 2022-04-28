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
        $this->surveillance = false;
        $this->nbEchecConnexionJournalierBB = 0;
        $this->nbEchecConnexionJournalier = 0;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set url.
     *
     * @param string $url
     *
     * @return SiteConnexion
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set codeLive.
     *
     * @param string|null $codeLive
     *
     * @return SiteConnexion
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
     * Set disponibilite.
     *
     * @param int $disponibilite
     *
     * @return SiteConnexion
     */
    public function setDisponibilite($disponibilite)
    {
        $this->disponibilite = $disponibilite;

        return $this;
    }

    /**
     * Get disponibilite.
     *
     * @return int
     */
    public function getDisponibilite()
    {
        return $this->disponibilite;
    }

    /**
     * Set accesDistant.
     *
     * @param array $accesDistant
     *
     * @return SiteConnexion
     */
    public function setAccesDistant($accesDistant)
    {
        $this->accesDistant = $accesDistant;

        return $this;
    }

    /**
     * Get accesDistant.
     *
     * @return array
     */
    public function getAccesDistant()
    {
        return $this->accesDistant;
    }

    /**
     * Set typeConnexion.
     *
     * @param array $typeConnexion
     *
     * @return SiteConnexion
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
     * Set dateEchecConnexion.
     *
     * @param \DateTime|null $dateEchecConnexion
     *
     * @return SiteConnexion
     */
    public function setDateEchecConnexion($dateEchecConnexion = null)
    {
        $this->dateEchecConnexion = $dateEchecConnexion;

        return $this;
    }

    /**
     * Get dateEchecConnexion.
     *
     * @return \DateTime|null
     */
    public function getDateEchecConnexion()
    {
        return $this->dateEchecConnexion;
    }

    /**
     * Set dureeEchecConnexionJournalier.
     *
     * @param \DateTime|null $dureeEchecConnexionJournalier
     *
     * @return SiteConnexion
     */
    public function setDureeEchecConnexionJournalier($dureeEchecConnexionJournalier = null)
    {
        $this->dureeEchecConnexionJournalier = $dureeEchecConnexionJournalier;

        return $this;
    }

    /**
     * Get dureeEchecConnexionJournalier.
     *
     * @return \DateTime|null
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
     * @param \DateTime|null $dateEchecConnexionBB
     *
     * @return SiteConnexion
     */
    public function setDateEchecConnexionBB($dateEchecConnexionBB = null)
    {
        $this->dateEchecConnexionBB = $dateEchecConnexionBB;

        return $this;
    }

    /**
     * Get dateEchecConnexionBB.
     *
     * @return \DateTime|null
     */
    public function getDateEchecConnexionBB()
    {
        return $this->dateEchecConnexionBB;
    }

    /**
     * Set dureeEchecConnexionJournalierBB.
     *
     * @param \DateTime|null $dureeEchecConnexionJournalierBB
     *
     * @return SiteConnexion
     */
    public function setDureeEchecConnexionJournalierBB($dureeEchecConnexionJournalierBB = null)
    {
        $this->dureeEchecConnexionJournalierBB = $dureeEchecConnexionJournalierBB;

        return $this;
    }

    /**
     * Get dureeEchecConnexionJournalierBB.
     *
     * @return \DateTime|null
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

}
