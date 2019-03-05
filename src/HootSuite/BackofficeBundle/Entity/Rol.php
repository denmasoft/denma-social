<?php

namespace HootSuite\BackofficeBundle\Entity;

use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Rol
 *
 * @ORM\Table(name="rol", uniqueConstraints={@ORM\UniqueConstraint(name="Id", columns={"Id"})})
 * @ORM\Entity(repositoryClass="HootSuite\BackofficeBundle\Entity\RolRepository")
 */
class Rol implements RoleInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="Id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="role", type="string", length=255, unique=true)
     */
    private $role;
    
        /**
     * @var string
     *
     * @ORM\Column(name="alias", type="string", length=255, unique=true)
     */
    private $alias;
    
    
            /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     */
    private $slug;


/**
     * @ORM\ManyToMany(targetEntity="Usuario", mappedBy="roles")
     */
    private $users;
    
    public function __construct()
    {
        $this->users = new ArrayCollection();
    }
    
    public function addUsuario(Usuario $usuarios)
    {
        $usuarios->addRole( $this );
        $this->users[] = $usuarios;
    
        return $this;
    }
    
     public function removeUsuario(Usuario $usuarios)
    {
        $this->users->removeElement($usuarios);
    }
    
    public function getUsuarios()
    {
        return $this->users;
    }


    public function getRole()
    {
        return $this->role;
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
     * Set rol
     *
     * @param string $rol
     * @return Rol
     */
    public function setRol($rol)
    {
        $this->rol = $rol;

        return $this;
    }

    /**
     * Get rol
     *
     * @return string 
     */
    public function getRol()
    {
        return $this->rol;
    }
    
    public function setAlias($alias)
    {
        $this->alias  = $alias;
        return $this;
    }
    
    public function getAlias()
    {
        return $this->alias;
    }
    
        public function setSlug($slug)
    {
        $this->slug  = $slug;
        return $this;
    }
    
    public function getSlug()
    {
        return $this->slug;
    }
}
