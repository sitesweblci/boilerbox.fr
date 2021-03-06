<?php
// src/Lci/BoilerBoxBundle/Entity/FichierSiteBA.php

namespace Lci\BoilerBoxBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;



/**
 * @ORM\Entity
 * @ORM\Table(name="fichier_site_ba")
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Entity\FichierSiteBARepository")
 * @ORM\HasLifecycleCallbacks()
*/

class FichierSiteBA {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"groupContact"})
    */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"groupContact"})
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
     * @Groups({"groupContact"})
    */
    protected $alt;

    /**
     * @Assert\File(maxSize="20000000", uploadErrorMessage="Erreur d'importation du fichier", maxSizeMessage="Fichier trop volumineux (max:20Mo)")
    */
    protected $file;

    /**
     *
     * @Assert\NotBlank(message="Veuillez uploader le(s) fichier(s) pdf.")
     * @ORM\ManyToOne(targetEntity="Lci\BoilerBoxBundle\Entity\SiteBA", inversedBy="fichiersJoint")
    */
    protected $siteBA;


	/**
     * @ORM\Column(type="boolean", name="archive")
     *
    */
    protected $archive;


    protected $tempFilename;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $userInitiateur;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateImportation;



    /**
     * Constructor
     */
	public function __construct() {
    	$this->archive = false;
		$this->dateImportation = new \Datetime();
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
     * @return FichierSiteBA
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
     * @return FichierSiteBA
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
     * @return FichierSiteBA
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

    /**
     * Set siteBA
     *
     * @param \Lci\BoilerBoxBundle\Entity\SiteBA $siteBA
     * @return FichierSiteBA
     */
    public function setSiteBA(\Lci\BoilerBoxBundle\Entity\SiteBA $siteBA = null)
    {
        $this->siteBA = $siteBA;

        return $this;
    }

    /**
     * Get siteBA
     *
     * @return \Lci\BoilerBoxBundle\Entity\SiteBA 
     */
    public function getSiteBA()
    {
        return $this->siteBA;
    }



    public function getFile(){
        return $this->file;
    }


    public function setFile(UploadedFile $file=null){
        $this->file = $file;
    }




    public  function getUploadDir() {
        return 'uploads/bonsAttachement/fichiersDesSitesBA';
    }

    public function getUploadRootDir(){
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }


    /**
     * Set archive.
     *
     * @param bool $archive
     *
     * @return FichierSiteBA
     */
    public function setArchive($archive)
    {
        $this->archive = $archive;

        return $this;
    }

    /**
     * Get archive.
     *
     * @return bool
     */
    public function getArchive()
    {
        return $this->archive;
    }

    public function getUserInitiateur(): ?string
    {
        return $this->userInitiateur;
    }

    public function setUserInitiateur(string $userInitiateur): self
    {
        $this->userInitiateur = $userInitiateur;

        return $this;
    }

    public function getDateImportation(): ?\DateTimeInterface
    {
        return $this->dateImportation;
    }

    public function setDateImportation(\DateTimeInterface $dateImportation): self
    {
        $this->dateImportation = $dateImportation;

        return $this;
    }






    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
    */
    public function preUpload(){
        if (null == $this->file){
            return;
        }
        //   Si le fichier n'a pas d??j?? ??t?? d??plac??, donc si son url est null
        if (empty($this->url)) {
            $this->extension = $this->file->guessExtension();
            if ($this->extension === null) {
                $this->extension = $this->file->guessClientExtension();
                // Si aucune extension de fichier n'est trouv?? ou devin??. R??cup??ration par expression r??guli??re
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
        // D??placement du fichier apr??s l'insertion des donn??es en base.
        $this->file->move($this->getUploadRootDir(), $this->siteBA->getId().'_'.$this->url);
    }



    /**
     * @ORM\PreRemove()
    */
    public function preRemoveUpload(){
        // Sauvegarde du nom du fichier sur le serveur. Il sera supprim?? lorsque l'entit?? sera effac??e de la base de donn??e
        $this->tempFilename = $this->getUploadRootDir().'/'.$this->siteBA->getId().'_'.$this->url;
    }


    /**
     * @ORM\PostRemove
    */
    public function removeUpload(){
        if (file_exists($this->tempFilename)){
            unlink($this->tempFilename);
        }
    }


}
