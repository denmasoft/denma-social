<?php

namespace HootSuite\BackofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Table(name="file")
 * @ORM\Entity()
 */
class File
{
  /**
   * @var integer $id
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;
    
  /**
    * @ORM\ManyToOne(targetEntity="Usuario", inversedBy="files")
    * @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
    */
  private $usuario;
  
  /** 
    * @var string $path
    * @ORM\Column(type="string", length=255)
    */
  protected $path;
  
  /** 
    * @var string $extension
    * @ORM\Column(type="string", length=10)
    */
  protected $extension;
  
  /** 
    * @var datetime $created_at
    * @ORM\Column(type="datetime")
    */
  protected $created_at;
  

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
     * Set path
     *
     * @param string $path
     * @return File
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set extension
     *
     * @param string $extension
     * @return File
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
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return File
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set usuario
     *
     * @param \HootSuite\BackofficeBundle\Entity\Usuario $usuario
     * @return File
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
