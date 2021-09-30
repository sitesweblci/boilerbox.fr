<?php
// src/Lci/BoilerBoxBundle/Entity/Role.php
namespace Lci\BoilerBoxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * Role
 *
 * @ORM\Table(name="role")
 * @ORM\Entity(repositoryClass="Lci\BoilerBoxBundle\Entity\RoleRepository")
 * @UniqueEntity("role")
*/

class Role {
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", options={"unsigned:true"})
	 * @ORM\GeneratedValue(strategy="AUTO")
	*/
	protected $id;

	/**
	 * @var string
     * @Assert\NotBlank()
	 *
	 * @ORM\Column(type="string", length=255, nullable=false)
	*/
	protected $role;

	/**
	 * @var string
     * @Assert\NotBlank()
	 *
	 * @ORM\Column(type="text")
	*/
	protected $description;

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
     * Set role
     *
     * @param string $role
     * @return Role
     */
    public function setRole($role)
    {
        $this->role = 'ROLE_'.strtoupper($role);

        return $this;
    }

    /**
     * Get role
     *
     * @return string 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Role
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }
}
