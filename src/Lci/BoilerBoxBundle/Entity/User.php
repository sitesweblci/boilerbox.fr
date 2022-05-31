<?php
// Lci/BoilerBoxBundle/Entity/User.php
// Fichier de validsation : # src/Lci/BoilerBoxBundle/Resources/config/validation.yml

namespace Lci\BoilerBoxBundle\Entity;

use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * User
 * @ORM\Entity
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Entity\UserRepository")
 * @UniqueEntity("username")
 * @ORM\HasLifecycleCallbacks
*/
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

	/**
	* @var string
	* 
 	* @ORM\Column(name="totpKey", type="string", length=16, nullable=true)
	*
	*/
	protected $totpKey;

    /**
	 * Paramètres defini dans Base User et réécri pour permettre sa récupération en ajax / json  / serializer
     * @var string
	 * @Groups({"groupSite"})
     */
    protected $username;


	/**
	 *
	 * @var string 
	 *
	 * @ORM\Column(name="qrCode", type="string", nullable=true)
	 *	
	*/
	protected $qrCode;
	

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=255)
     * @Groups({"groupSite"})
     *
     * @Assert\NotBlank()
    */
    protected $label;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     * @Groups({"groupSite"})
     *
     * @Assert\NotBlank()
    */
    protected $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="prenom", type="string", length=255)
     * @Groups({"groupSite"})
     *
     * @Assert\NotBlank()
    */
    protected $prenom;


    /**
     * @ORM\ManyToMany(targetEntity="Lci\BoilerBoxBundle\Entity\Site", inversedBy="users", cascade={"persist"})
     * @ORM\OrderBy({"affaire" = "ASC"})
    */
    protected $site;

    /**
     * One User can have many problems to solve
     * @ORM\OneToMany(targetEntity="ProblemeTechnique", mappedBy="user", cascade={"remove"})
    */
    protected $problemeTechnique;


    /**
     * Un utilisateur peut être la cible de plusieurs bons d'attachements
     *
     * @ORM\OneToMany(targetEntity="BonsAttachement", mappedBy="user", cascade={"remove"})
    */
    protected $bonsAttachement;

    /**
     * Un utilisateur peut être la cible de plusieurs bons d'attachements
     *
     * @ORM\OneToMany(targetEntity="BonsAttachement", mappedBy="userInitiateur", cascade={"remove"})
    */
    protected $bonsAttachementInitiateur;



	/**
	 * Un utilisateur peut valider plusieurs bons
	 *
	 * @ORM\OneToMany(targetEntity="Validation", mappedBy="user", cascade={"remove"})
	*/
	protected $validations;


    /**
    * @var string
    *
    * @ORM\Column(name="couleur", type="string")
	* @Groups({"groupSite"})
    *
    */
    protected $couleur;

    /**
    * @var string
    *
    * @ORM\Column(name="informations", type="text", nullable=true)
    *
    */
    protected $informations;


    /**
     * Un utilisateur peux avoir créé plusieurs commentaires
     * @ORM\OneToMany(targetEntity="CommentairesSite", mappedBy="user")
    */
    protected $commentaires;

    /**
     * Un utilisateur peux avoir importé plusieurs fichiers
     * @ORM\OneToMany(targetEntity="FichierV2", cascade={"remove"}, mappedBy="user")
    */
    protected $fichiersV2;



	/**
	 *
	 * @var boolean
	 *
	 * @ORM\Column(name="modePrive", type="boolean")
     * @Groups({"groupSite"})
	*/
	protected $modePrive;

    /**
     * @var string
     *
     * @ORM\Column(name="langue", type="string", length=10)
     * @Groups({"groupSite"})
     *
     * @Assert\NotBlank()
    */
    protected $langue;


    /**
     *
     * @var boolean
     *
     * @ORM\Column(name="cgu_accepte", type="boolean")
     * @Groups({"groupSite"})
     */
    protected $cguAccepte;

    public function __construct()
    {
       	parent::__construct();
       	if (empty($this->roles)) {
         	$this->roles[] = 'ROLE_USER';
       	}
		$this->enabled = true;
		$this->site = new ArrayCollection();
  		$this->problemeTechnique = new ArrayCollection();
  		$this->bonsAttachement = new ArrayCollection();
  		$this->bonsAttachementInitiateur = new ArrayCollection();
  		$this->validations = new ArrayCollection();
		$this->couleur = '#004080';
		$this->langue = 'fr';
  		$this->commentaires = new ArrayCollection();
  		$this->fichiersV2 = new ArrayCollection();
		$this->cguAccepte = false;
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
     * Set totpKey
     *
     * @param string $totpKey
     * @return User
     */
    public function setTotpKey($key)
    {
        $this->totpKey = $key;

        return $this;
    }

    /**
     * Get totpKey
     *
     * @return string
     */
    public function getTotpKey()
    {
        return $this->totpKey;
    }

    /**
     * Set qrCode
     *
     * @param string $qrCode
     * @return User
     */
    public function setQrCode($qrCode)
    {
        $this->qrCode = $qrCode;

        return $this;
    }

    /**
     * Get qrCode
     *
     * @return string
     */
    public function getQrCode()
    {
        return $this->qrCode;
    }





    /**
     * Add site
     *
     * @param \Lci\BoilerBoxBundle\Entity\Site $site
     * @return User
     */
    public function addSite(\Lci\BoilerBoxBundle\Entity\Site $site)
    {
        $this->site[] = $site;
		$site->addUser($this);
        return $this;
    }

    /**
     * Remove site
     *
     * @param \Lci\BoilerBoxBundle\Entity\Site $site
     */
    public function removeSite(\Lci\BoilerBoxBundle\Entity\Site $site)
    {
        $this->site->removeElement($site);
    }

    /**
     * Get site
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Set label
     *
     * @param string $label
     * @return User
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string 
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Add problemeTechnique
     *
     * @param \Lci\BoilerBoxBundle\Entity\ProblemeTechnique $problemeTechnique
     * @return User
     */
    public function addProblemeTechnique(\Lci\BoilerBoxBundle\Entity\ProblemeTechnique $problemeTechnique)
    {
        $this->problemeTechnique[] = $problemeTechnique;

        return $this;
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

    /**
     * Get problemeTechnique
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProblemeTechnique()
    {
        return $this->problemeTechnique;
    }


    public function setUsername($username)
    {
        $this->username = $username;
		$this->label = $username;
        return $this;
    }


    /**
     * Add bonsAttachement
     *
     * @param \Lci\BoilerBoxBundle\Entity\BonsAttachement $bonsAttachement
     * @return User
     */
    public function addBonsAttachement(\Lci\BoilerBoxBundle\Entity\BonsAttachement $bonsAttachement)
    {
        $this->bonsAttachement[] = $bonsAttachement;

        return $this;
    }

    /**
     * Remove bonsAttachement
     *
     * @param \Lci\BoilerBoxBundle\Entity\BonsAttachement $bonsAttachement
     */
    public function removeBonsAttachement(\Lci\BoilerBoxBundle\Entity\BonsAttachement $bonsAttachement)
    {
        $this->bonsAttachement->removeElement($bonsAttachement);
    }

    /**
     * Get bonsAttachement
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBonsAttachement()
    {
        return $this->bonsAttachement;
    }

    /**
     * Add validations
     *
     * @param \Lci\BoilerBoxBundle\Entity\Validation $validations
     * @return User
     */
    public function addValidation(\Lci\BoilerBoxBundle\Entity\Validation $validations)
    {
        $this->validations[] = $validations;

        return $this;
    }

    /**
     * Remove validations
     *
     * @param \Lci\BoilerBoxBundle\Entity\Validation $validations
     */
    public function removeValidation(\Lci\BoilerBoxBundle\Entity\Validation $validations)
    {
        $this->validations->removeElement($validations);
    }

    /**
     * Get validations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getValidations()
    {
        return $this->validations;
    }

    /**
     * Add bonsAttachementInitiateur
     *
     * @param \Lci\BoilerBoxBundle\Entity\BonsAttachement $bonsAttachementInitiateur
     * @return User
     */
    public function addBonsAttachementInitiateur(\Lci\BoilerBoxBundle\Entity\BonsAttachement $bonsAttachementInitiateur)
    {
        $this->bonsAttachementInitiateur[] = $bonsAttachementInitiateur;

        return $this;
    }

    /**
     * Remove bonsAttachementInitiateur
     *
     * @param \Lci\BoilerBoxBundle\Entity\BonsAttachement $bonsAttachementInitiateur
     */
    public function removeBonsAttachementInitiateur(\Lci\BoilerBoxBundle\Entity\BonsAttachement $bonsAttachementInitiateur)
    {
        $this->bonsAttachementInitiateur->removeElement($bonsAttachementInitiateur);
    }

    /**
     * Get bonsAttachementInitiateur
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBonsAttachementInitiateur()
    {
        return $this->bonsAttachementInitiateur;
    }

	public function myGetRoles() {
                        		$str_roles = "";
                        		$pattern_role = '#ROLE_(.*)#';
                        		$replacement = '$1';
                        		foreach($this->getRoles() as $role) {
                        			$nouveau_role = strtolower(preg_replace($pattern_role, $replacement, $role));
                        			if ($nouveau_role != 'user') {
                        				$str_roles .= strtolower(preg_replace($pattern_role, $replacement, $role)).', ';	
                        			}
                        		}
                        		if (! empty($str_roles) ) {
                        			return substr(trim($str_roles),0,-1);
                        		} else {
                        			return 'client';
                        		}
                        	}

	public function myGetRolesHtml() {
                        		$str_roles = $this->myGetRoles();
                        		$pattern_admin = '#admin#';
                        		$pattern_responsable_parc = '#responsable_parc#';
                        		$pattern_gestion_ba = '#gestion_ba#';
                                $pattern_saisie_ba = '#saisie_ba#';
                        		$pattern_technicien = '#technicien#';
                                $pattern_auto_enquete = '#auto_enquete#';
                        		$pattern_client = '#client#';
                        
                        		$pattern_services = '#service#';
                        		$pattern_secreteriat = '#secreteriat#';
                        
                        		if (preg_match($pattern_auto_enquete, $str_roles)) {
                        			return "<span class='auto_enquete'>".$str_roles."</span>";
                                } elseif (preg_match($pattern_admin, $str_roles)) {
                        			return "<span class='administrateur'>".$str_roles."</span>";
                        		} elseif (preg_match($pattern_responsable_parc, $str_roles)) {
                                    return "<span class='responsable_de_parc'>".$str_roles."</span>";
                        		} elseif (preg_match($pattern_saisie_ba, $str_roles)) {
                                    return "<span class='saisie_ba'>".$str_roles."</span>";
                                } elseif (preg_match($pattern_services, $str_roles)) {
                                    return "<span class='services'>".$str_roles."</span>";
                                }elseif (preg_match($pattern_secreteriat, $str_roles)) {
                                    return "<span class='secreteriat'>".$str_roles."</span>";
                                }elseif (preg_match($pattern_gestion_ba, $str_roles)) {
                                    return "<span class='gestion_ba'>".$str_roles."</span>";
                                } elseif (preg_match($pattern_technicien, $str_roles)) {
                                    return "<span class='technicien'>".$str_roles."</span>";
                                } else {
                                	if (preg_match($pattern_client, $str_roles)) {
                                	    return "<span class='client'>".$str_roles."</span>";
                                	} else {
                        				return "<span style='color:black'>".$str_roles."</span>";
                        			}
                        		}
                        	}

    /**
     * Set couleur.
     *
     * @param string $couleur
     *
     * @return User
     */
    public function setCouleur($couleur)
    {
        $this->couleur = $couleur;

        return $this;
    }

    /**
     * Get couleur.
     *
     * @return string
     */
    public function getCouleur()
    {
        return $this->couleur;
    }


    /**
     * Set informations
     *
     * @param string $informations
     *
     * @return User
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
     * Add commentaire.
     *
     * @param \Lci\BoilerBoxBundle\Entity\CommentairesSite $commentaire
     *
     * @return User
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
     * Add fichiersV2
     *
     * @param \Lci\BoilerBoxBundle\Entity\FichierV2 $fichierV2
     *
     * @return User
     */
    public function addFichierV2(\Lci\BoilerBoxBundle\Entity\FichierV2 $fichierV2)
    {
        $this->fichiersV2[] = $fichierV2;

        return $this;
    }

    /**
     * Remove fichierV2.
     *
     * @param \Lci\BoilerBoxBundle\Entity\FichierV2 $fichierV2
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeFichierV2(\Lci\BoilerBoxBundle\Entity\FichierV2 $fichierV2)
    {
        return $this->fichiersV2->removeElement($fichierV2);
    }

    /**
     * Get fichiersV2.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFichiersV2()
    {
        return $this->fichiersV2;
    }



    /**
     * Set nom.
     *
     * @param string $nom
     *
     * @return User
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom.
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set prenom.
     *
     * @param string $prenom
     *
     * @return User
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom.
     *
     * @return string
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set modePrive.
     *
     * @param bool $modePrive
     *
     * @return User
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
     * Set cguAccepte.
     *
     * @param bool $cguAccepte
     *
     * @return User
     */
    public function setCguAccepte($cguAccepte)
    {
        $this->cguAccepte = $cguAccepte;

        return $this;
    }

    /**
     * Get cguAccepte.
     *
     * @return bool
     */
    public function getCguAccepte()
    {
        return $this->cguAccepte;
    }

    /**
     * Set langue.
     *
     * @param string $langue
     *
     * @return User
     */
    public function setLangue($langue)
    {
        $this->langue = $langue;

        return $this;
    }

    /**
     * Get langue.
     *
     * @return string
     */
    public function getLangue()
    {
        return $this->langue;
    }

    /**
     * Add fichiersV2.
     *
     * @param \Lci\BoilerBoxBundle\Entity\FichierV2 $fichiersV2
     *
     * @return User
     */
    public function addFichiersV2(\Lci\BoilerBoxBundle\Entity\FichierV2 $fichiersV2)
    {
        $this->fichiersV2[] = $fichiersV2;

        return $this;
    }

    /**
     * Remove fichiersV2.
     *
     * @param \Lci\BoilerBoxBundle\Entity\FichierV2 $fichiersV2
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeFichiersV2(\Lci\BoilerBoxBundle\Entity\FichierV2 $fichiersV2)
    {
        return $this->fichiersV2->removeElement($fichiersV2);
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }
}
