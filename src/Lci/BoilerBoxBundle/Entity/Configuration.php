<?php
namespace Lci\BoilerBoxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;




/**
 * Configuration
 *
 * @ORM\Entity
 * @ORM\Table(name="configuration")
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
     * @ORM\Column(type="string", length=255)
	 * @Groups({"groupSite"})
    */
    protected $valeur;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Lci\BoilerBoxBundle\Entity\Site", inversedBy="configurations")
    */
    protected $site;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Lci\BoilerBoxBundle\Entity\SiteConnexion", inversedBy="configurations")
    */
    protected $siteConnexion;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Lci\BoilerBoxBundle\Entity\SiteAutres", inversedBy="configurations")
    */
    protected $siteAutres;




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
     * Set parametre
     *
     * @param string $parametre
     * @return Configuration
     */
    public function setParametre($parametre)
    {
        $this->parametre = $parametre;

        return $this;
    }

    /**
     * Get parametre
     *
     * @return string 
     */
    public function getParametre()
    {
        return $this->parametre;
    }

    /**
     * Set valeur
     *
     * @param string $valeur
     * @return Configuration
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
     * Set site
     *
     * @param \Lci\BoilerBoxBundle\Entity\Site $site
     * @return Configuration
     */
    public function setSite(\Lci\BoilerBoxBundle\Entity\Site $site = null)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site
     *
     * @return \Lci\BoilerBoxBundle\Entity\Site
     */
    public function getSite()
    {
        return $this->site;
    }


    /**
     * Set siteConnexion.
     *
     * @param \Lci\BoilerBoxBundle\Entity\SiteConnexion|null $siteConnexion
     *
     * @return Configuration
     */
    public function setSiteConnexion(\Lci\BoilerBoxBundle\Entity\SiteConnexion $siteConnexion = null)
    {
        $this->siteConnexion = $siteConnexion;

        return $this;
    }

    /**
     * Get siteConnexion.
     *
     * @return \Lci\BoilerBoxBundle\Entity\SiteConnexion|null
     */
    public function getSiteConnexion()
    {
        return $this->siteConnexion;
    }

    /**
     * Set siteAutres.
     *
     * @param \Lci\BoilerBoxBundle\Entity\SiteAutres|null $siteAutres
     *
     * @return Configuration
     */
    public function setSiteAutres(\Lci\BoilerBoxBundle\Entity\SiteAutres $siteAutres = null)
    {
        $this->siteAutres = $siteAutres;

        return $this;
    }

    /**
     * Get siteAutres.
     *
     * @return \Lci\BoilerBoxBundle\Entity\SiteAutres|null
     */
    public function getSiteAutres()
    {
        return $this->siteAutres;
    }
}
