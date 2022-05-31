<?php 
//src/Lci/BoilerBoxBundle/Entity/Fichier.php

// Fichiers joints aux bons d'attachements

namespace Lci\BoilerBoxBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * @ORM\Entity
 * @ORM\Table(name="fichier")
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Entity\FichierRepository")
 * @ORM\HasLifecycleCallbacks
*/
class Fichier {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
    */
    protected $url;

    /**
     * @ORM\Column(type="string", length=4, name="extension")
     *
    */
    protected $extension;

  	/**
   	 * @ORM\Column(name="alt", type="string", length=255)
   	*/
  	protected $alt;

	/**
     * @ORM\column(name="archive", type="boolean", nullable=true)
    */
	protected $archive;

	/**
	 * @ORM\Column(name="informations", type="text", nullable=true)
    */
	protected $informations;


    /**
     * @Assert\NotBlank(message="Veuillez uploader le(s) fichier(s) pdf.")
     * @Assert\File(maxSize="20000000", uploadErrorMessage="Erreur d'importation du fichier", maxSizeMessage="Fichier trop volumineux (max:20Mo)")
    */
	protected $file;

	/**
	 *
 	 * @ORM\ManyToOne(targetEntity="Lci\BoilerBoxBundle\Entity\BonsAttachement", inversedBy="fichiersPdf")
	*/
	protected $bonAttachement;


	protected $tempFilename;




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
			$this->url = md5(uniqId()).'.'.$this->extension;
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
		$this->file->move($this->getUploadRootDir(), $this->bonAttachement->getId().'_'.$this->url);
	}
	
    /**
     * @ORM\PreRemove()
    */
    public function preRemoveUpload(){
		// Sauvegarde du nom du fichier sur le serveur. Il sera supprimé lorsque l'entité sera effacée de la base de donnée
		$this->tempFilename = $this->getUploadRootDir().'/'.$this->bonAttachement->getId().'_'.$this->url;	
	}


	/**
	 * @ORM\PostRemove
	*/
	public function removeUpload(){
		if (file_exists($this->tempFilename)){
			unlink($this->tempFilename);
		}	
	}


	public  function getUploadDir() {
		return 'uploads/bonsAttachement/fichiersDesBons';
	}	

    public function getUploadRootDir(){
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }



    /**
     * Set bonAttachement
     *
     * @param \Lci\BoilerBoxBundle\Entity\BonsAttachement $bonAttachement
     * @return Fichier
     */
    public function setBonAttachement(\Lci\BoilerBoxBundle\Entity\BonsAttachement $bonAttachement = null)
    {
        $this->bonAttachement = $bonAttachement;

        return $this;
    }

    /**
     * Get bonAttachement
     *
     * @return \Lci\BoilerBoxBundle\Entity\BonsAttachement 
     */
    public function getBonAttachement()
    {
        return $this->bonAttachement;
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
}
