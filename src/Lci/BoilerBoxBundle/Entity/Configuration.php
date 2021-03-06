<?php
namespace Lci\BoilerBoxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;




/**
 * Configuration
 *
 * @ORM\Entity
 * @ORM\Table(name="configuration")
 * @UniqueEntity("parametre")
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Entity\ConfigurationRepository")
*/
class Configuration {
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
     * @ORM\Column(type="string", length=255)
	 * @Groups({"groupSite"})
    */
	protected $parametre;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
	 * @Groups({"groupSite"})
    */
    protected $valeur;

    /**
     *
     * @ORM\OneToMany(targetEntity="Lci\BoilerBoxBundle\Entity\SiteConfiguration", mappedBy="configuration")
    */
    protected $siteConfigurations;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"groupSite"})
    */
    protected $type;    // Type de configuration : Valeur acceptée :  Site, SiteConnexion, SiteAutre


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->siteConfigurations = new \Doctrine\Common\Collections\ArrayCollection();
		$this->type = 'global';
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
     * Set parametre.
     *
     * @param string $parametre
     *
     * @return Configuration
     */
    public function setParametre($parametre)
    {
        $this->parametre = $parametre;

        return $this;
    }

    /**
     * Get parametre.
     *
     * @return string
     */
    public function getParametre()
    {
        return $this->parametre;
    }

    /**
     * Set valeur.
     *
     * @param string $valeur
     *
     * @return Configuration
     */
    public function setValeur($valeur)
    {
        $this->valeur = $valeur;

        return $this;
    }

    /**
     * Get valeur.
     *
     * @return string
     */
    public function getValeur()
    {
        return $this->valeur;
    }

    /**
     * Add siteConfiguration.
     *
     * @param \Lci\BoilerBoxBundle\Entity\SiteConfiguration $siteConfiguration
     *
     * @return Configuration
     */
    public function addSiteConfiguration(\Lci\BoilerBoxBundle\Entity\SiteConfiguration $siteConfiguration)
    {
        $this->siteConfigurations[] = $siteConfiguration;

        return $this;
    }

    /**
     * Remove siteConfiguration.
     *
     * @param \Lci\BoilerBoxBundle\Entity\SiteConfiguration $siteConfiguration
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeSiteConfiguration(\Lci\BoilerBoxBundle\Entity\SiteConfiguration $siteConfiguration)
    {
        return $this->siteConfigurations->removeElement($siteConfiguration);
    }

    /**
     * Get siteConfigurations.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSiteConfigurations()
    {
        return $this->siteConfigurations;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return Configuration
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

}
