<?php 
//src/Lci/BoilerBoxBundle/Entity/FichierJoint.php

namespace Lci\BoilerBoxBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
//use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * @ORM\Entity
 * @ORM\Table(name="fichier_joint")
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Entity\FichierJointRepository")
 * @ORM\HasLifecycleCallbacks
*/
class FichierJoint {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;

    /**
     * Many files can references one problem
     * @ORM\ManyToMany(targetEntity="ProblemeTechnique", cascade={"persist"}, inversedBy="fichiersJoint")
     * @ORM\JoinColumn(name="problemeTechnique_id", referencedColumnName="id", nullable=false)
    */
    protected $problemeTechnique;

    /**
     * @ORM\Column(type="string", length=255)
     *
    */
    protected $url;

    /**
     * @ORM\Column(type="date", name="dateImport")
     * @Assert\Date()
     *
    */
	protected $dateImport;

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
     * @Assert\File(maxSize="20000000", uploadErrorMessage="Erreur d'importation du fichier", maxSizeMessage="Fichier trop volumineux (max:20Mo)")
    */
	protected $file;

	protected $targetDir;

	public function __construct(){
      		$this->targetDir = $this->getUploadDir();
      		$this->dateImport = new \Datetime();
      		$this->problemeTechnique = new \Doctrine\Common\Collections\ArrayCollection();
      	}

	public function getUploadDir(){
      		return __DIR__."/../../../../web/uploads/problemes/";
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
     * Set dateImport
     *
     * @param \DateTime $dateImport
     * @return FichierJoint
     */
    public function setDateImport($dateImport)
    {
        $this->dateImport = $dateImport;

        return $this;
    }

    /**
     * Get dateImport
     *
     * @return \DateTime 
     */
    public function getDateImport()
    {
        return $this->dateImport;
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
     * Add problemeTechnique
     *
     * @param \Lci\BoilerBoxBundle\Entity\ProblemeTechnique $problemeTechnique
     * @return FichierJoint
     */
    public function addProblemeTechnique(\Lci\BoilerBoxBundle\Entity\ProblemeTechnique $problemeTechnique = null)
    {
        $this->problemeTechnique[] = $problemeTechnique;
        return $this;
    }


    /**
     * Remove problemeTechnique
     *
     * @param \Lci\BoilerBoxBundle\Entity\ProblemeTechnique $problemeTechnique
     */
    public function removeProblemeTechniqueJoint(\Lci\BoilerBoxBundle\Entity\ProblemeTechnique $problemeTechnique)
    {
        $this->problemeTechnique->removeElement($problemeTechnique);
    }


    /**
     * Get problemeTechnique
     *
     * @return \Lci\BoilerBoxBundle\Entity\ProblemeTechnique 
     */
    public function getProblemeTechnique()
    {
        return $this->problemeTechnique;
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

	public function setFile($file=null){
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
      			$this->alt = $this->file->getClientOriginalName();
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
      		// création d'un identifiant unique pour le fichier
      		// Si le fichier n'a pas déjà été déplacé
      		if (! file_exists($this->targetDir.$this->url)) {
      			$this->file->move($this->targetDir, $this->url);
      		}
      	}
	
	/**
	 * @ORM\PostRemove
	*/
	public function remove(){
      		if ($file_exists($this->getPath())){
      			unlink($this->getPath());
      		}	
      	}

	public function getPath(){
      		return $this->getUploadDir().$this->url;
      	}

    /**
     * Remove problemeTechnique
     *
     * @param \Lci\BoilerBoxBundle\Entity\ProblemeTechnique $problemeTechnique
     */
    public function removeProblemeTechnique(\Lci\BoilerBoxBundle\Entity\ProblemeTechnique $problemeTechnique)
    {
        $this->problemeTechnique->removeElement($problemeTechnique);
    }
}
