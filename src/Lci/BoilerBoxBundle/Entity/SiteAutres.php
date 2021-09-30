<?php
//src/Lci/BoilerBoxBundle/Entity/SiteAutres.php

namespace Lci\BoilerBoxBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Site
 * @ORM\Entity
 * @ORM\Table(name="site_autres")
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Entity\SiteRepository")
 * @ORM\HasLifecycleCallbacks
*/
class SiteAutres
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
     * @ORM\OneToMany(targetEntity="Lci\BoilerBoxBundle\Entity\Configuration", mappedBy="siteAutres", cascade={"persist", "remove"})
	 * @Groups({"groupSite"})
     *
    */
    protected $configurations;

	/**
     *
     * @ORM\OneToOne(targetEntity="Lci\BoilerBoxBundle\Entity\Site", mappedBy="siteAutres", cascade={"persist"})
	*/
	protected $site;


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
     * Constructor
     */
    public function __construct()
    {
        $this->configurations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set site.
     *
     * @param \Lci\BoilerBoxBundle\Entity\Site|null $site
     *
     * @return SiteAutres
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
