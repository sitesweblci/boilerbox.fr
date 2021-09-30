<?php
//src/Lci/BoilerBoxBundle/Entity/Zoneadmin.php

namespace Lci\BoilerBoxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Zoneadmin
 * @ORM\Entity
 * @ORM\Table(name="zoneadmin")
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Entity\ZoneadminRepository")
*/
class Zoneadmin
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
     * @ORM\Column(type="string",length=255)
    */
    protected $login;

    /**
     * @var string
     *
     * @ORM\Column(type="string",length=255)
    */
    protected $password;


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
     * Set login
     *
     * @param string $login
     * @return Zoneadmin
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Get login
     *
     * @return string 
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return Zoneadmin
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }
}
