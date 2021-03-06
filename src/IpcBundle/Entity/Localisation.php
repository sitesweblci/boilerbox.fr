<?php

namespace Ipc\ProgBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

// Une localisation peut pointer sur 	 	plusieurs fichiers de données
//						plusieurs données

// Plusieurs localisations peuvent pointer sur un site

    /**
     * Localisation
     *
     * @ORM\Table(name="t_localisation")
     * @ORM\Entity(repositoryClass="Ipc\ProgBundle\Entity\LocalisationRepository")
     * @ORM\HasLifecycleCallbacks
    */
class Localisation
{
    /** 
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="adresse_modbus", type="integer")
    */
    protected $adresseModbus;

    /**
     * @var integer
     *
     * @ORM\Column(name="numero_localisation", type="integer", nullable=false)
    */
    protected $numeroLocalisation;

    /**
     * @var string
     *
     * @ORM\Column(name="adresse_ip", type="string", length=255, nullable=false) 
    */
    protected $adresseIp;

    /** 
     * @var string
     *
     * @ORM\Column(name="designation", type="string", length=255)
    */
    protected $designation;

   /**
    * @var string
    *
    * @ORM\Column(name="login_ftp", type="string", length=50)
   */
   protected $loginFtp;


   /**
    * @var string
    *
    * @ORM\Column(name="password_ftp", type="string", length=255)
   */
   protected $passwordFtp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_horodatage", type="datetime")
     */
    protected $lastHorodatage;


    /**
     * @ORM\ManyToOne(targetEntity="Ipc\ProgBundle\Entity\Site", inversedBy="localisations", cascade={"persist"})
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id", nullable=false)
    */
    protected $site;

    /**
     * @ORM\ManyToOne(targetEntity="Ipc\ProgBundle\Entity\Mode", inversedBy="localisations", cascade={"persist"})
     * @ORM\JoinColumn(name="mode_id", referencedColumnName="id")
    */
    protected $mode;

    /**
     * @ORM\ManyToOne(targetEntity="Ipc\ProgBundle\Entity\TypeGenerateur", inversedBy="localisations", cascade={"persist"})
     * @ORM\JoinColumn(name="typeGenerateur_id", referencedColumnName="id", nullable=false)
    */
    protected $typeGenerateur;


    /**
     * @ORM\OneToMany(targetEntity="Ipc\ProgBundle\Entity\Fichier", mappedBy="localisation", cascade={"remove"})
    */
    protected $fichiers;

    /**
     * @ORM\OneToMany(targetEntity="Ipc\ProgBundle\Entity\EtatParametre", mappedBy="localisation", cascade={"remove"})
    */
    protected $etatParametre;


    /**
     * @ORM\OneToMany(targetEntity="Ipc\ProgBundle\Entity\InfosLocalisation", mappedBy="localisation")
    */
    protected $infosLocalisation;

    /**
     * @ORM\OneToMany(targetEntity="Ipc\ProgBundle\Entity\DonneeLive", mappedBy="localisation")
    */
    protected $donneesLive;

    /**
     * @ORM\ManyToMany(targetEntity="Ipc\ProgBundle\Entity\Module", cascade={"persist"})
    */
    protected $modules;


    /**
     * @ORM\OneToMany(targetEntity="Ipc\ProgBundle\Entity\Rapport", mappedBy="localisation", cascade={"remove"})
    */
    protected $rapport;





    /**
     * @ORM\PreRemove()
    */
    public function avantSupp()
    {
	//	Décrémentation du nombre d'automates du site d'une localisation quand la localisation va être supprimée
	$this->site->setNbAutomates($this->site->getNbAutomates()-1);
    }
	

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fichiers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->lastHorodatage = new \Datetime();
		$this->modules = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set id
     *
     * @param integer $id
     * @return Localisation
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * Set adresseIp
     *
     * @param string $adresseIp
     * @return Localisation
     */
    public function setAdresseIp($adresseIp)
    {
        $this->adresseIp = $adresseIp;

        return $this;
    }

    /**
     * Get adresseIp
     *
     * @return string 
     */
    public function getAdresseIp()
    {
        return $this->adresseIp;
    }


    /**
     * Get fichier
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFichier()
    {
        return $this->fichier;
    }

    /**
     * Set site
     *
     * @param \Ipc\ProgBundle\Entity\Site $site
     * @return Localisation
     */
    public function setSite(\Ipc\ProgBundle\Entity\Site $site)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site
     *
     * @return \Ipc\ProgBundle\Entity\Site 
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Set numeroLocalisation
     *
     * @param integer $numeroLocalisation
     * @return Localisation
     */
    public function setNumeroLocalisation($numeroLocalisation)
    {
        $this->numeroLocalisation = $numeroLocalisation;
        return $this;
    }

    /**
     * Get numeroLocalisation
     *
     * @return integer 
     */
    public function getNumeroLocalisation()
    {
        return $this->numeroLocalisation;
    }

    /**
     * Add fichiers
     *
     * @param \Ipc\ProgBundle\Entity\Fichier $fichiers
     * @return Localisation
     */
    public function addFichier(\Ipc\ProgBundle\Entity\Fichier $fichiers)
    {
        $this->fichiers[] = $fichiers;

        return $this;
    }

    /**
     * Remove fichiers
     *
     * @param \Ipc\ProgBundle\Entity\Fichier $fichiers
     */
    public function removeFichier(\Ipc\ProgBundle\Entity\Fichier $fichiers)
    {
        $this->fichiers->removeElement($fichiers);
    }

    /**
     * Get fichiers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFichiers()
    {
        return $this->fichiers;
    }

    /**
     * Set designation
     *
     * @param string $designation
     * @return Localisation
     */
    public function setDesignation($designation)
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * Get designation
     *
     * @return string
     */
    public function getDesignation()
    {
        return $this->designation;
    }

    /**
     * Set loginFtp
     *
     * @param string $loginFtp
     * @return Localisation
     */
    public function setLoginFtp($loginFtp)
    {
        $this->loginFtp = $loginFtp;

        return $this;
    }

    /**
     * Get loginFtp
     *
     * @return string
     */
    public function getLoginFtp()
    {
        return $this->loginFtp;
    }

    /**
     * Set passwordFtp
     *
     * @param string $passwordFtp
     * @return Localisation
     */
    public function setPasswordFtp($passwordFtp)
    {
        $this->passwordFtp = $passwordFtp;

        return $this;
    }

    /**
     * Get passwordFtp
     *
     * @return string
     */
    public function getPasswordFtp()
    {
        return $this->passwordFtp;
    }

    /**
     * Set lastHorodatage
     *
     * @param \DateTime $lastHorodatage
     * @return Localisation
     */
    public function setLastHorodatage($lastHorodatage)
    {
        $this->lastHorodatage = $lastHorodatage;

        return $this;
    }

   /**
    * Get lastHorodatageStr
    *
    * @return string
   */
    public function getLastHorodatageStr()
    {
        return $this->lastHorodatage->format('YmdHis');
    }


    /**
     * Get lastHorodatage
     *
     * @return \DateTime 
     */
    public function getLastHorodatage()
    {
        return $this->lastHorodatage;
    }

    /**
     * Set mode
     *
     * @param \Ipc\ProgBundle\Entity\Mode $mode
     * @return Localisation
     */
    public function setMode(\Ipc\ProgBundle\Entity\Mode $mode = null)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * Get mode
     *
     * @return \Ipc\ProgBundle\Entity\Mode 
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Add infosLocalisation
     *
     * @param \Ipc\ProgBundle\Entity\InfosLocalisation $infosLocalisation
     * @return Localisation
     */
    public function addInfosLocalisation(\Ipc\ProgBundle\Entity\InfosLocalisation $infosLocalisation)
    {
        $this->infosLocalisation[] = $infosLocalisation;

        return $this;
    }

    /**
     * Remove infosLocalisation
     *
     * @param \Ipc\ProgBundle\Entity\InfosLocalisation $infosLocalisation
     */
    public function removeInfosLocalisation(\Ipc\ProgBundle\Entity\InfosLocalisation $infosLocalisation)
    {
        $this->infosLocalisation->removeElement($infosLocalisation);
    }

    /**
     * Get infosLocalisation
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInfosLocalisation()
    {
        return $this->infosLocalisation;
    }

    /**
     * Add modules
     *
     * @param \Ipc\ProgBundle\Entity\Module $modules
     * @return Localisation
     */
    public function addModule(\Ipc\ProgBundle\Entity\Module $modules)
    {
        $this->modules[] = $modules;

        return $this;
    }

    /**
     * Remove modules
     *
     * @param \Ipc\ProgBundle\Entity\Module $modules
     */
    public function removeModule(\Ipc\ProgBundle\Entity\Module $modules)
    {
        $this->modules->removeElement($modules);
    }

    /**
     * Get modules
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getModules()
    {
        return $this->modules;
    }


    public function resetModules()
    {
		$this->modules = new \Doctrine\Common\Collections\ArrayCollection();
		return $this;
    }


    /**
     * Add rapport
     *
     * @param \Ipc\ProgBundle\Entity\Rapport $rapport
     * @return Localisation
     */
    public function addRapport(\Ipc\ProgBundle\Entity\Rapport $rapport)
    {
        $this->rapport[] = $rapport;

        return $this;
    }

    /**
     * Remove rapport
     *
     * @param \Ipc\ProgBundle\Entity\Rapport $rapport
     */
    public function removeRapport(\Ipc\ProgBundle\Entity\Rapport $rapport)
    {
        $this->rapport->removeElement($rapport);
    }

    /**
     * Get rapport
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRapport()
    {
        return $this->rapport;
    }

    /**
     * Add donneesLive
     *
     * @param \Ipc\ProgBundle\Entity\DonneeLive $donneesLive
     * @return Localisation
     */
    public function addDonneesLive(\Ipc\ProgBundle\Entity\DonneeLive $donneesLive)
    {
        $this->donneesLive[] = $donneesLive;

        return $this;
    }

    /**
     * Remove donneesLive
     *
     * @param \Ipc\ProgBundle\Entity\DonneeLive $donneesLive
     */
    public function removeDonneesLive(\Ipc\ProgBundle\Entity\DonneeLive $donneesLive)
    {
        $this->donneesLive->removeElement($donneesLive);
    }

    /**
     * Get donneesLive
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDonneesLive()
    {
        return $this->donneesLive;
    }


    /**
     * Set typeGenerateur
     *
     * @param \Ipc\ProgBundle\Entity\TypeGenerateur $typeGenerateur
     * @return Localisation
     */
    public function setTypeGenerateur(\Ipc\ProgBundle\Entity\TypeGenerateur $typeGenerateur)
    {
        $this->typeGenerateur = $typeGenerateur;

        return $this;
    }

    /**
     * Get typeGenerateur
     *
     * @return \Ipc\ProgBundle\Entity\TypeGenerateur 
     */
    public function getTypeGenerateur()
    {
        return $this->typeGenerateur;
    }


    /**
     * Set adresseModbus
     *
     * @param integer $adresseModbus
     * @return Localisation
     */
    public function setAdresseModbus($adresseModbus)
    {
        $this->adresseModbus = $adresseModbus;

        return $this;
    }

    /**
     * Get adresseModbus
     *
     * @return integer 
     */
    public function getAdresseModbus()
    {
        return $this->adresseModbus;
    }



    /**
     * Add etatParametre
     *
     * @param \Ipc\ProgBundle\Entity\EtatParametre $etatParametre
     * @return Localisation
     */
    public function addEtatParametre(\Ipc\ProgBundle\Entity\EtatParametre $etatParametre)
    {
        $this->etatParametre[] = $etatParametre;

        return $this;
    }

    /**
     * Remove etatParametre
     *
     * @param \Ipc\ProgBundle\Entity\EtatParametre $etatParametre
     */
    public function removeEtatParametre(\Ipc\ProgBundle\Entity\EtatParametre $etatParametre)
    {
        $this->etatParametre->removeElement($etatParametre);
    }




    /**
     * Get etatParametre
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEtatParametre()
    {
        return $this->etatParametre;
    }
}
