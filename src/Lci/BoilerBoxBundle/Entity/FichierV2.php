<?php 
//src/Lci/BoilerBoxBundle/Entity/Fichier.php

namespace Lci\BoilerBoxBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;



/**
 * @ORM\Entity
 * @ORM\Table(name="fichier_v2")
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Entity\FichierV2Repository")
 * @ORM\HasLifecycleCallbacks
*/
class FichierV2 {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
	 * @Groups({"groupSite"})
    */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"groupSite"})
    */
    protected $url;

    /**
     * @ORM\Column(type="string", length=4, name="extension")
	 * @Groups({"groupSite"})
     *
    */
    protected $extension;

  	/**
   	 * @ORM\Column(name="alt", type="string", length=255)
	 * @Groups({"groupSite"})
   	*/
  	protected $alt;

    /**
     * @var datetime
     *
     * @ORM\Column(type="datetime", name="dateImportation")
     * @Groups({"groupSite"})
    */
	protected $dtImportation;

	/**
     * @ORM\column(name="archive", type="boolean", nullable=true)
    */
	protected $archive;

	/**
	 * @ORM\Column(name="informations", type="text", nullable=true)
	 * @Groups({"groupSite"})
    */
	protected $informations;

	/**
     * @ORM\Column(type="string", name="filename")
	 * @Groups({"groupSite"})
     *
    */
    protected $filename;
	

    /**
     * @Assert\NotBlank(message="Veuillez uploader le(s) fichier(s) pdf.")
     * @Assert\File(maxSize="20000000", uploadErrorMessage="Erreur d'importation du fichier", maxSizeMessage="Fichier trop volumineux (max:20Mo)")
    */
	protected $file;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Lci\BoilerBoxBundle\Entity\Site", inversedBy="fichiers")
    */
    protected $site;

	/* Variable utilisée pour enregistrer temporairement le nom du fichier afin de pouvoir le supprimer lorsque l'entité est supprimé */
	protected $tempFilename;

    /**
     * Many Commentaires can be send to a site
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"}, inversedBy="fichiersV2")
     * @Groups({"groupSite"})
    */
    protected $user;

    /**
     *
     * @var boolean
     *
     * @ORM\Column(name="modePrive", type="boolean")
  	 * @Groups({"groupSite"})
    */
    protected $modePrive;



    public function __construct() {
        $this->dtImportation = new \Datetime();
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
     * @return FichierJoint
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
     * Set extension
     *
     * @param string $extension
     * @return FichierJoint
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get extension
     *
     * @return string 
     */
    public function getExtension()
    {
        return $this->extension;
    }


    /**
     * Set filename
     *
     * @param string $filename
     * @return FichierJoint
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }




    /**
     * Set alt
     *
     * @param string $alt
     * @return FichierJoint
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;

        return $this;
    }

    /**
     * Get alt
     *
     * @return string 
     */
    public function getAlt()
    {
        return $this->alt;
    }


	public function getFile(){
		return $this->file;
	}

	public function setFile(UploadedFile $file=null){
		$this->file = $file;
	}





	/**
     * @ORM\PrePersist()
	 * @ORM\PreUpdate()
 	*/
	public function preUpload(){
		if (null == $this->file){
			return;
		}
		//	 Si le fichier n'a pas déjà été déplacé, donc si son url est null
		if (empty($this->url)) {
			$this->extension = $this->file->guessExtension();
			if ($this->extension === null) {
				$this->extension = $this->file->guessClientExtension();
				// Si aucune extension de fichier n'est trouvé ou deviné. Récupération par expression régulière
				if ($this->extension == null) {
					$pattern = '#\.(*)$#';
					$tab_retour_exp = array();
					if (preg_match('#\.(\w+)$#', $this->file->getClientOriginalName(), $tab_retour_exp)) {
						$this->extension = $tab_retour_exp[1];
					}
				}
			}
			// Nom du fichier dans les pages html
			$this->alt = $this->file->getClientOriginalName();

			// Nom du fichier sur le serveur : Unique. 
			$this->filename = uniqId() . '.' . $this->extension;

			// Url où atteindre le site
			$this->url = $this->getDownloadDirectory() . $this->filename;
		}
	}

	
	/**
	 * @ORM\PostPersist()
	 * @ORM\PostUpdate()
	*/
	public function upload(){
		if (null == $this->file){
			return;
		}
		// Déplacement du fichier après l'insertion des données en base.
		$this->file->move($this->getUploadsDirectory(), $this->filename);
	}
	
    /**
     * @ORM\PreRemove()
    */
    public function preRemoveUpload(){
		// Sauvegarde du nom du fichier sur le serveur. Il sera supprimé lorsque l'entité sera effacée de la base de donnée
		$this->tempFilename = $this->getUploadsDirectory().$this->filename;	
	}


	/**
	 * @ORM\PostRemove
	*/
	public function removeUpload(){
		if (file_exists($this->tempFilename)){
			unlink($this->tempFilename);
		}	
	}

	public function getDownloadDirectory() {
		if ($this->site != null) {
            return '/uploads/fichiersDuSite/';
         } else {
            return '/uploads/';
        }
	}

	public function getUploadsDirectory() {
		if ($this->site != null) {
			return __DIR__ . '/../../../../web/uploads/fichiersDuSite/';
		 } else {
			return __DIR__ . '/../../../../web/uploads/';
		}
	}




    /**
     * Set archive
     *
     * @param boolean $archive
     * @return Fichier
     */
    public function setArchive($archive)
    {
        $this->archive = $archive;

        return $this;
    }

    /**
     * Get archive
     *
     * @return boolean 
     */
    public function getArchive()
    {
        return $this->archive;
    }

    /**
     * Set informations
     *
     * @param string $informations
     * @return Fichier
     */
    public function setInformations($informations)
    {
        $this->informations = $informations;

        return $this;
    }

    /**
     * Get informations
     *
     * @return string 
     */
    public function getInformations()
    {
        return $this->informations;
    }



    /**
     * Set Site
     *
     * @param \Lci\BoilerBoxBundle\Entity\Site $site
     * @return Fichier
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
     * Set dtImportation
     *
     * @param \DateTime $dtImportation
     *
     * @return Site
     */
    public function setDtImportation($dtImportation)
    {
        $this->dtImportation = $dtImportation;

        return $this;
    }

    /**
     * Get dtImportation
     *
     * @return \DateTime
     */
    public function getDtImportation()
    {
        return $this->dtImportation;
    }



    /**
     * Set modePrive.
     *
     * @param bool $modePrive
     *
     * @return FichierV2
     */
    public function setModePrive($modePrive)
    {
        $this->modePrive = $modePrive;

        return $this;
    }

    /**
     * Get modePrive.
     *
     * @return bool
     */
    public function getModePrive()
    {
        return $this->modePrive;
    }


    /**
     * Set user.
     *
     * @param \Lci\BoilerBoxBundle\Entity\User|null $user
     *
     * @return FichierV2
     */
    public function setUser(\Lci\BoilerBoxBundle\Entity\User $user = null)
    {
        $this->user = $user;
        $this->user->addFichierV2($this);
        return $this;
    }

    /**
     * Get user.
     *
     * @return \Lci\BoilerBoxBundle\Entity\User|null
     */
    public function getUser()
    {
        return $this->user;
    }

}
