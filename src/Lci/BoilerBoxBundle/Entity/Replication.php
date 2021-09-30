<?php
namespace Lci\BoilerBoxBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * ReplicationCloud
 * @ORM\Entity
 * @ORM\Table(name="replication")
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Repository\ReplicationRepository")
 * @ORM\HasLifecycleCallbacks
*/
class Replication
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"groupSite"})
     */
    protected $id;


    /**
     *
     * @ORM\OneToOne(targetEntity="Lci\BoilerBoxBundle\Entity\Site", inversedBy="replication", cascade={"persist"})
    */
    protected $site;


 	/**
     * @var boolean
     *
    */
    protected $etat;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=5)
    */
    protected $etat_sql;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
    */
    protected $message_etat_sql;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=5)
    */
    protected $etat_io;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
    */
    protected $message_etat_io;

	
    /**
     * @var integer
     *
     * @ORM\Column(type="integer", length=255)
    */
    protected $retard;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
    */
    protected $message_erreur;


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
     * Set etatSql.
     *
     * @param string $etatSql
     *
     * @return Replication
     */
    public function setEtatSql($etatSql)
    {
        $this->etat_sql = $etatSql;

        return $this;
    }

    /**
     * Get etatSql.
     *
     * @return string
     */
    public function getEtatSql()
    {
        return $this->etat_sql;
    }

    /**
     * Set messageEtatSql.
     *
     * @param string $messageEtatSql
     *
     * @return Replication
     */
    public function setMessageEtatSql($messageEtatSql)
    {
        $this->message_etat_sql = $messageEtatSql;

        return $this;
    }

    /**
     * Get messageEtatSql.
     *
     * @return string
     */
    public function getMessageEtatSql()
    {
        return $this->message_etat_sql;
    }

    /**
     * Set etatIo.
     *
     * @param string $etatIo
     *
     * @return Replication
     */
    public function setEtatIo($etatIo)
    {
        $this->etat_io = $etatIo;

        return $this;
    }

    /**
     * Get etatIo.
     *
     * @return string
     */
    public function getEtatIo()
    {
        return $this->etat_io;
    }

    /**
     * Set messageEtatIo.
     *
     * @param string $messageEtatIo
     *
     * @return Replication
     */
    public function setMessageEtatIo($messageEtatIo)
    {
        $this->message_etat_io = $messageEtatIo;

        return $this;
    }

    /**
     * Get messageEtatIo.
     *
     * @return string
     */
    public function getMessageEtatIo()
    {
        return $this->message_etat_io;
    }

    /**
     * Set retard.
     *
     * @param int $retard
     *
     * @return Replication
     */
    public function setRetard($retard)
    {
        $this->retard = $retard;

        return $this;
    }

    /**
     * Get retard.
     *
     * @return int
     */
    public function getRetard()
    {
        return $this->retard;
    }

    /**
     * Set messageErreur.
     *
     * @param string $messageErreur
     *
     * @return Replication
     */
    public function setMessageErreur($messageErreur)
    {
        $this->message_erreur = $messageErreur;

        return $this;
    }

    /**
     * Get messageErreur.
     *
     * @return string
     */
    public function getMessageErreur()
    {
        return $this->message_erreur;
    }

    /**
     * Set site.
     *
     * @param \Lci\BoilerBoxBundle\Entity\Site|null $site
     *
     * @return Replication
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
}
