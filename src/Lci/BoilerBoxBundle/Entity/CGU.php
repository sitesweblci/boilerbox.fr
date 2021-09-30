<?php
//src/Lci/BoilerBoxBundle/Entity/Fichier.php

namespace Lci\BoilerBoxBundle\Entity;

use DateTime;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;



/**
 * @ORM\Entity
 * @ORM\Table(name="cgu")
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Entity\CGURepository")
 * @ORM\HasLifecycleCallbacks
 */
class CGU {
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
     * @ORM\Column(type="string", name="filename")
     * @Groups({"groupSite"})
     *
     */
    protected $filename;

    /**
     * @var datetime
     *
     * @ORM\Column(type="datetime", name="dateImportation")
     * @Groups({"groupSite"})
     */
    protected $dtImportation;

    /**
     * @ORM\Column(name="version", type="string", length=50)
     * @Groups({"groupSite"})
     */
    protected $version;

    /**
     *
     * @var boolean
     *
     * @ORM\Column(name="cgu_courant", type="boolean")
     * @Groups({"groupSite"})
     */
    protected $cguCourant;

    /**
     *
     * @var boolean
     *
     * @ORM\Column(name="cgu_obligatoire", type="boolean")
     * @Groups({"groupSite"})
     */
    protected $cguObligatoire;

    /**
     * @Assert\NotBlank(message="Veuillez uploader le(s) fichier(s) pdf.")
     * @Assert\File(maxSize="20000000", uploadErrorMessage="Erreur d'importation du fichier", maxSizeMessage="Fichier trop volumineux (max:20Mo)")
     */
    protected $file;

    /* Variable utilisée pour enregistrer temporairement le nom du fichier afin de pouvoir le supprimer lorsque l'entité est supprimé */
    protected $tempFilename;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"groupSite"})
     */
    protected $user;

    public function __construct() {
        $this->dtImportation = new \Datetime();
    }

    /**
     * Get id
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set url
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }


    /**
     * Set extension
     */
    public function setExtension(string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get extension
     */
    public function getExtension(): ?string
    {
        return $this->extension;
    }

    /**
     * Set alt
     */
    public function setAlt(string $alt): self
    {
        $this->alt = $alt;

        return $this;
    }

    /**
     * Get alt
     */
    public function getAlt(): ?string
    {
        return $this->alt;
    }


    public function getFile()
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file=null): self
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload(): void
    {
        if (null === $this->file){
            return;
        }
        //	 Si le fichier n'a pas déjà été déplacé, donc si son url est null
        if (empty($this->url)) {
            $this->extension = $this->file->guessExtension();
            if ($this->extension === null) {
                $this->extension = $this->file->guessClientExtension();
                // Si aucune extension de fichier n'est trouvé ou deviné. Récupération par expression régulière
                if ($this->extension === null) {
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
    public function upload(): void
    {
        if (null === $this->file){
            return;
        }
        // Déplacement du fichier après l'insertion des données en base.
        $this->file->move($this->getUploadsDirectory(), $this->filename);
    }

    /**
     * @ORM\PreRemove()
     */
    public function preRemoveUpload(): void
    {
        // Sauvegarde du nom du fichier sur le serveur. Il sera supprimé lorsque l'entité sera effacée de la base de donnée
        $this->tempFilename = $this->getUploadsDirectory().$this->filename;
    }


    /**
     * @ORM\PostRemove
     */
    public function removeUpload(): void
    {
        if (file_exists($this->tempFilename)){
            unlink($this->tempFilename);
        }
    }

    public function getDownloadDirectory(): string
    {
        return '/uploads/cgu/';
    }

    public function getUploadsDirectory(): string
    {
        return __DIR__ . '/../../../../web/uploads/cgu/';
    }

    /**
     * Set version
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * Get cguCourant
     */
    public function getCguCourant(): ?bool
    {
        return $this->cguCourant;
    }

    /**
     * Set cguCourant
     */
    public function setCguCourant(bool $cguCourant): self
    {
        $this->cguCourant = $cguCourant;

        return $this;
    }

    /**
     * Get cguObligatoire
     */
    public function getCguObligatoire(): ?bool
    {
        return $this->cguObligatoire;
    }

    /**
     * Set cguObligatoire
     */
    public function setCguObligatoire(bool $cguObligatoire): self
    {
        $this->cguObligatoire = $cguObligatoire;

        return $this;
    }

    /**
     * Set filename
     */
    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * Set dtImportation
     */
    public function setDtImportation(DateTime $dtImportation): self
    {
        $this->dtImportation = $dtImportation;

        return $this;
    }

    /**
     * Get dtImportation
     */
    public function getDtImportation(): ?DateTime
    {
        return $this->dtImportation;
    }

    /**
     * Set user
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     */
    public function getUser(): ?User
    {
        return $this->user;
    }
}
