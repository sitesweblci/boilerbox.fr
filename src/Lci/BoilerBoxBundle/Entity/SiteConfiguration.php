<?php
//src/Lci/BoilerBoxBundle/Entity/SiteConfiguration.php

namespace Lci\BoilerBoxBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * SiteConfiguration
 * @ORM\Entity
 * @ORM\Table(name="site_configuration")
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Entity\SiteConfigurationRepository")
 * @ORM\HasLifecycleCallbacks
*/
class SiteConfiguration
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"groupSite"})
     */
    protected $id;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Lci\BoilerBoxBundle\Entity\Configuration", inversedBy="siteConfigurations", cascade={"persist"})
	 * @Groups({"groupSite"})
     *
    */
    protected $configuration;

	/**
     *
     * @ORM\ManyToOne(targetEntity="Lci\BoilerBoxBundle\Entity\Site", inversedBy="siteConfigurations", cascade={"persist"})
	*/
	protected $site;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"groupSite"})
    */
    protected $valeur;

	/**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"groupSite"})
    */
    protected $type;	// Type de configuration : Valeur acceptée :  Site, SiteConnexion, SiteAutre

	
	public function __toString()
    {
        return $this->valeur;
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
     * Set valeur.
     *
     * @param string $valeur
     *
     * @return SiteConfiguration
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
     * Set type.
     *
     * @param string $type
     *
     * @return SiteConfiguration
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

    /**
     * Set configuration.
     *
     * @param \Lci\BoilerBoxBundle\Entity\Configuration|null $configuration
     *
     * @return SiteConfiguration
     */
    public function setConfiguration(\Lci\BoilerBoxBundle\Entity\Configuration $configuration = null)
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * Get configuration.
     *
     * @return \Lci\BoilerBoxBundle\Entity\Configuration|null
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Set site.
     *
     * @param \Lci\BoilerBoxBundle\Entity\Site|null $site
     *
     * @return SiteConfiguration
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
