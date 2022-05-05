<?php
//src/Lci/BoilerBoxBundle/Entity/Site.php
// Fichier de validsation : # src/Lci/BoilerBoxBundle/Resources/config/validation.yml

namespace Lci\BoilerBoxBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * Site
 * @ORM\Entity
 * @UniqueEntity("affaire")
 * @ORM\Table(name="site")
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Entity\SiteRepository")
 * @ORM\HasLifecycleCallbacks
*/
class Site
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"groupSite"})
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="Lci\BoilerBoxBundle\Entity\User", mappedBy="site")
     * @ORM\OrderBy({"label" = "ASC"})
    */
    protected $users;


    /**
     * @var string
     *
     * @ORM\Column(type="string",length=255)
	 * @Groups({"groupSite"})
    */
    protected $intitule;

    /**
     * @var string
     *
     * @ORM\Column(type="string",length=20, unique=true)
     * @Groups({"groupSite"})
    */
    protected $affaire;

    /**
     * @var string
     *
     * @ORM\Column(type="string",length=10)
     * @Groups({"groupSite"})
    */
    protected $version;

    /**
     * @var string
     *
	 * Variable provenant des sites du Cloud
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"groupSite"})
    */
    protected $nbDbDonnees;

    /** 
     * @var datetime
     *
     * @ORM\Column(type="datetime", name="dateAccess", nullable=true)
     * @Groups({"groupSite"})
    */
    protected $dateAccess;

    /**
     * @var datetime
     *
     * @ORM\Column(type="datetime", name="dateAccessSucceded", nullable=true)
     * @Groups({"groupSite"})
    */
    protected $dateAccessSucceded;

    /**
     *
     * @ORM\Column(type="string", nullable=true)
     *
    */
    protected $typeAccessSucceeded;

    /**
     * One Site can have many Modules
     * @ORM\OneToMany(targetEntity="Module", mappedBy="site")
    */
    protected $module;

    /**
     * One Site can have many Commentaires
     * @ORM\OneToMany(targetEntity="CommentairesSite", mappedBy="site")
     * @Groups({"groupSite"})
	 * @MaxDepth(2)
    */
    protected $commentaires;

	/**
     *
     * @ORM\OneToMany(targetEntity="Lci\BoilerBoxBundle\Entity\FichierV2", mappedBy="site", cascade={"persist", "remove"})
     * @Groups({"groupSite"})
     *
    */
    protected $fichiers;

    /**
     *
     * @ORM\OneToOne(targetEntity="Lci\BoilerBoxBundle\Entity\Replication", mappedBy="site", cascade={"persist", "remove"})
     * @Groups({"groupSite"})
    */
    protected $replication;


    /**
     *
     * @ORM\OneToMany(targetEntity="Lci\BoilerBoxBundle\Entity\SiteConfiguration", mappedBy="site", cascade={"persist", "remove"})
     * @Groups({"groupSite"})
    */
    protected $siteConfigurations;

    /**
     *
     * @ORM\OneToOne(targetEntity="Lci\BoilerBoxBundle\Entity\SiteConnexion", inversedBy="site", cascade={"persist", "remove"})
     * @Groups({"groupSite"})
    */
    protected $siteConnexion;



	/**
	 *
	 * @ORM\Column(type="string")
	 *
	*/
	protected $mailSended;

    /**
     * @var datetime
     *
     * @ORM\Column(type="datetime", name="dateMailSended", nullable=true)
    */
    protected $dateMailSended;


	/**
     * @var integer
     *
     * @ORM\Column(type="integer", name="erreurScript", nullable=true)
    */
    protected $erreur_script;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->module = new \Doctrine\Common\Collections\ArrayCollection();
        $this->commentaires = new \Doctrine\Common\Collections\ArrayCollection();
        $this->fichiers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->siteConfigurations = new \Doctrine\Common\Collections\ArrayCollection();
		$this->mailSended = "non";
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
     * Set intitule.
     *
     * @param string $intitule
     *
     * @return Site
     */
    public function setIntitule($intitule)
    {
        $this->intitule = $intitule;

        return $this;
    }

    /**
     * Get intitule.
     *
     * @return string
     */
    public function getIntitule()
    {
        return $this->intitule;
    }

    /**
     * Set affaire.
     *
     * @param string $affaire
     *
     * @return Site
     */
    public function setAffaire($affaire)
    {
        $this->affaire = $affaire;

        return $this;
    }

    /**
     * Get affaire.
     *
     * @return string
     */
    public function getAffaire()
    {
        return $this->affaire;
    }

    /**
     * Set version.
     *
     * @param string $version
     *
     * @return Site
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set nbDbDonnees.
     *
     * @param string|null $nbDbDonnees
     *
     * @return Site
     */
    public function setNbDbDonnees($nbDbDonnees = null)
    {
        $this->nbDbDonnees = $nbDbDonnees;

        return $this;
    }

    /**
     * Get nbDbDonnees.
     *
     * @return string|null
     */
    public function getNbDbDonnees()
    {
        return $this->nbDbDonnees;
    }

    /**
     * Set dateAccess.
     *
     * @param \DateTime|null $dateAccess
     *
     * @return Site
     */
    public function setDateAccess($dateAccess = null)
    {
        $this->dateAccess = $dateAccess;

        return $this;
    }

    /**
     * Get dateAccess.
     *
     * @return \DateTime|null
     */
    public function getDateAccess()
    {
        return $this->dateAccess;
    }

    /**
     * Set dateAccessSucceded.
     *
     * @param \DateTime|null $dateAccessSucceded
     *
     * @return Site
     */
    public function setDateAccessSucceded($dateAccessSucceded = null)
    {
        $this->dateAccessSucceded = $dateAccessSucceded;

        return $this;
    }

    /**
     * Get dateAccessSucceded.
     *
     * @return \DateTime|null
     */
    public function getDateAccessSucceded()
    {
        return $this->dateAccessSucceded;
    }

    /**
     * Set typeAccessSucceeded.
     *
     * @param string|null $typeAccessSucceeded
     *
     * @return Site
     */
    public function setTypeAccessSucceeded($typeAccessSucceeded = null)
    {
        $this->typeAccessSucceeded = $typeAccessSucceeded;

        return $this;
    }

    /**
     * Get typeAccessSucceeded.
     *
     * @return string|null
     */
    public function getTypeAccessSucceeded()
    {
        return $this->typeAccessSucceeded;
    }

    /**
     * Set mailSended.
     *
     * @param string $mailSended
     *
     * @return Site
     */
    public function setMailSended($mailSended)
    {
        $this->mailSended = $mailSended;

        return $this;
    }

    /**
     * Get mailSended.
     *
     * @return string
     */
    public function getMailSended()
    {
        return $this->mailSended;
    }

    /**
     * Set dateMailSended.
     *
     * @param \DateTime|null $dateMailSended
     *
     * @return Site
     */
    public function setDateMailSended($dateMailSended = null)
    {
        $this->dateMailSended = $dateMailSended;

        return $this;
    }

    /**
     * Get dateMailSended.
     *
     * @return \DateTime|null
     */
    public function getDateMailSended()
    {
        return $this->dateMailSended;
    }

    /**
     * Set erreurScript.
     *
     * @param int|null $erreurScript
     *
     * @return Site
     */
    public function setErreurScript($erreurScript = null)
    {
        $this->erreur_script = $erreurScript;

        return $this;
    }

    /**
     * Get erreurScript.
     *
     * @return int|null
     */
    public function getErreurScript()
    {
        return $this->erreur_script;
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
     * Add module.
     *
     * @param \Lci\BoilerBoxBundle\Entity\Module $module
     *
     * @return Site
     */
    public function addModule(\Lci\BoilerBoxBundle\Entity\Module $module)
    {
        $this->module[] = $module;

        return $this;
    }

    /**
     * Remove module.
     *
     * @param \Lci\BoilerBoxBundle\Entity\Module $module
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeModule(\Lci\BoilerBoxBundle\Entity\Module $module)
    {
        return $this->module->removeElement($module);
    }

    /**
     * Get module.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Add commentaire.
     *
     * @param \Lci\BoilerBoxBundle\Entity\CommentairesSite $commentaire
     *
     * @return Site
     */
    public function addCommentaire(\Lci\BoilerBoxBundle\Entity\CommentairesSite $commentaire)
    {
        $this->commentaires[] = $commentaire;

        return $this;
    }

    /**
     * Remove commentaire.
     *
     * @param \Lci\BoilerBoxBundle\Entity\CommentairesSite $commentaire
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeCommentaire(\Lci\BoilerBoxBundle\Entity\CommentairesSite $commentaire)
    {
        return $this->commentaires->removeElement($commentaire);
    }

    /**
     * Get commentaires.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommentaires()
    {
        return $this->commentaires;
    }

    /**
     * Add fichier.
     *
     * @param \Lci\BoilerBoxBundle\Entity\FichierV2 $fichier
     *
     * @return Site
     */
    public function addFichier(\Lci\BoilerBoxBundle\Entity\FichierV2 $fichier)
    {
        $this->fichiers[] = $fichier;

		// On effectue la liaison inverse depuis le site.
        $fichier->setSite($this);

        return $this;
    }

    /**
     * Remove fichier.
     *
     * @param \Lci\BoilerBoxBundle\Entity\FichierV2 $fichier
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeFichier(\Lci\BoilerBoxBundle\Entity\FichierV2 $fichier)
    {
        return $this->fichiers->removeElement($fichier);
    }

    /**
     * Get fichiers.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFichiers()
    {
        return $this->fichiers;
    }

    /**
     * Set replication.
     *
     * @param \Lci\BoilerBoxBundle\Entity\Replication|null $replication
     *
     * @return Site
     */
    public function setReplication(\Lci\BoilerBoxBundle\Entity\Replication $replication = null)
    {
        $this->replication = $replication;

        return $this;
    }

    /**
     * Get replication.
     *
     * @return \Lci\BoilerBoxBundle\Entity\Replication|null
     */
    public function getReplication()
    {
        return $this->replication;
    }

    /**
     * Add siteConfiguration.
     *
     * @param \Lci\BoilerBoxBundle\Entity\SiteConfiguration $siteConfiguration
     *
     * @return Site
     */
    public function addSiteConfiguration(\Lci\BoilerBoxBundle\Entity\SiteConfiguration $siteConfiguration)
    {
        $this->siteConfigurations[] = $siteConfiguration;

		// On effectue la liaison inverse depuis le site.
        $siteConfiguration->setSite($this);

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
     * Set siteConnexion.
     *
     * @param \Lci\BoilerBoxBundle\Entity\SiteConnexion|null $siteConnexion
     *
     * @return Site
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
}
