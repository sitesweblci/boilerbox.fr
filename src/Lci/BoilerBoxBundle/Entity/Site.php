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
     * @ORM\OneToMany(targetEntity="Lci\BoilerBoxBundle\Entity\Configuration", mappedBy="site", cascade={"persist", "remove"})
	 * @Groups({"groupSite"})
     *
    */
    protected $configurations;

    /**
     *
     * @ORM\OneToOne(targetEntity="Lci\BoilerBoxBundle\Entity\SiteConnexion", inversedBy="site", cascade={"persist", "remove"})
	 * @Groups({"groupSite"})
    */
    protected $siteConnexion;

    /**
     *
     * @ORM\OneToOne(targetEntity="Lci\BoilerBoxBundle\Entity\SiteAutres", inversedBy="site", cascade={"persist", "remove"})
	 * @Groups({"groupSite"})
    */
    protected $siteAutres;

    /**
     *
     * @ORM\OneToOne(targetEntity="Lci\BoilerBoxBundle\Entity\Replication", mappedBy="site", cascade={"persist", "remove"})
     * @Groups({"groupSite"})
    */
    protected $replication;




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
        $this->module = new \Doctrine\Common\Collections\ArrayCollection();
		$this->users = new ArrayCollection();
  		$this->commentaires = new ArrayCollection();
  		$this->fichiers = new ArrayCollection();
  		$this->configurations = new ArrayCollection();
		$this->mailSended = "non";
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
     * Set intitule
     *
     * @param string $intitule
     * @return Site
     */
    public function setIntitule($intitule)
    {
        $this->intitule = $intitule;

        return $this;
    }

    /**
     * Get intitule
     *
     * @return string 
     */
    public function getIntitule()
    {
        return $this->intitule;
    }

    /**
     * Set affaire
     *
     * @param string $affaire
     * @return Site
     */
    public function setAffaire($affaire)
    {
        $this->affaire = strtoupper($affaire);

        return $this;
    }

    /**
     * Get affaire
     *
     * @return string 
     */
    public function getAffaire()
    {
        return $this->affaire;
    }


    /**
     * Set dateAccess
     *
     * @param \DateTime $dateAccess
     * @return Site
     */
    public function setDateAccess($dateAccess)
    {
        $this->dateAccess = $dateAccess;

        return $this;
    }

    /**
     * Get dateAccess
     *
     * @return \DateTime 
     */
    public function getDateAccess()
    {
        return $this->dateAccess;
    }

    /**
     * Set dateMailSended
     *
     * @param \DateTime $dateMailSended
     * @return Site
     */
    public function setDateMailSended($dateMailSended)
    {
        $this->dateMailSended = $dateMailSended;

        return $this;
    }

    /**
     * Get dateMailSended
     *
     * @return \DateTime
     */
    public function getDateMailSended()
    {
        return $this->dateMailSended;
    }





    /**
     * Set dateAccessSucceded
     *
     * @param \DateTime $dateAccessSucceded
     * @return Site
     */
    public function setDateAccessSucceded($dateAccessSucceded)
    {
        $this->dateAccessSucceded = $dateAccessSucceded;

        return $this;
    }

    /**
     * Get dateAccessSucceded
     *
     * @return \DateTime
     */
    public function getDateAccessSucceded()
    {
        return $this->dateAccessSucceded;
    }


    /**
     * Add module
     *
     * @param \Lci\BoilerBoxBundle\Entity\Module $module
     * @return Site
     */
    public function addModule(\Lci\BoilerBoxBundle\Entity\Module $module)
    {
        $this->module[] = $module;

        return $this;
    }

    /**
     * Remove module
     *
     * @param \Lci\BoilerBoxBundle\Entity\Module $module
     */
    public function removeModule(\Lci\BoilerBoxBundle\Entity\Module $module)
    {
        $this->module->removeElement($module);
    }

    /**
     * Get module
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

    /**
     * Set siteAutres.
     *
     * @param \Lci\BoilerBoxBundle\Entity\SiteAutres|null $siteAutres
     *
     * @return Site
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

    public function getReplication(): ?Replication
    {
        return $this->replication;
    }

    public function setReplication(?Replication $replication): self
    {
        $this->replication = $replication;

        return $this;
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
}
