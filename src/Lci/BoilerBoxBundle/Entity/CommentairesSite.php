<?php
//src/Lci/BoierBoxBundle/Entity/CommentairesSite

namespace Lci\BoilerBoxBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * CommentairesSite
 * @ORM\Entity
 * @ORM\Table(name="commentaires_site")
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Entity\CommentairesSiteRepository")
 */
class CommentairesSite
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"groupSite"})
     */
    private $id;

    /**
     * @var datetime
     *
     * @ORM\Column(type="datetime", name="dateCreation")
     * @Groups({"groupSite"})
    */
    protected $dtCreation;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     * @Groups({"groupSite"})
     */
    protected $commentaire;


    /**
     * Many Commentaires can be send to a site
     * @ORM\ManyToOne(targetEntity="Site", cascade={"persist"}, inversedBy="commentaires")
    */
    protected $site;

    /**
     * Many Commentaires can be send to a site
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"}, inversedBy="commentaires")
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
     * Set dtCreation.
     *
     * @param \DateTime $dtCreation
     *
     * @return CommentairesSite
     */
    public function setDtCreation($dtCreation)
    {
        $this->dtCreation = $dtCreation;

        return $this;
    }

    /**
     * Get dtCreation.
     *
     * @return \DateTime
     */
    public function getDtCreation()
    {
        return $this->dtCreation;
    }

    /**
     * Set commentaire.
     *
     * @param string $commentaire
     *
     * @return CommentairesSite
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get commentaire.
     *
     * @return string
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }


    /**
     * Set site.
     *
     * @param \Lci\BoilerBoxBundle\Entity\Site|null $site
     *
     * @return CommentairesSite
     */
    public function setSite(\Lci\BoilerBoxBundle\Entity\Site $site = null)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site.
     *
     * @return \Lci\BoilerBoxBundle\Entity\Site|null
     */
    public function getSite()
    {
        return $this->site;
    }


    /**
     * Set user.
     *
     * @param \Lci\BoilerBoxBundle\Entity\User|null $user
     *
     * @return CommentairesSite
     */
    public function setUser(\Lci\BoilerBoxBundle\Entity\User $user = null)
    {
        $this->user = $user;
		$this->user->addCommentaire($this);
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



    /**
     * Set modePrive.
     *
     * @param bool $modePrive
     *
     * @return CommentairesSite
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
}
