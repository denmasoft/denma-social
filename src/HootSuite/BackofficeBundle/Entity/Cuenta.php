<?php

namespace HootSuite\BackofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Table(name="cuenta")
 * @ORM\Entity(repositoryClass="CuentaRepository")
 */
class Cuenta
{
  /**
   * @var integer $id
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;
    
  /**
    * @ORM\ManyToOne(targetEntity="Usuario", inversedBy="campannas")
    * @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
    */
  private $usuario;
    
  /** 
    * @var string $nombre
    * @ORM\Column(type="string", length=255, nullable=true)
    */
  protected $nombre;
  

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
     * Set nombre
     *
     * @param string $nombre
     * @return Cuenta
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set usuario
     *
     * @param \HootSuite\BackofficeBundle\Entity\Usuario $usuario
     * @return Cuenta
     */
    public function setUsuario(\HootSuite\BackofficeBundle\Entity\Usuario $usuario = null)
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * Get usuario
     *
     * @return \HootSuite\BackofficeBundle\Entity\Usuario 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }
}
