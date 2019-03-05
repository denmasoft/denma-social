<?php

namespace HootSuite\BackofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Table(name="workspace")
 * @ORM\Entity(repositoryClass="WorkspaceRepository")
 */
class Workspace
{
  /**
   * @var integer $id
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;
    
  /**
    * @ORM\OneToOne(targetEntity="Usuario", inversedBy="workspace" )
    */
  private $usuario;
    
   /**
    * @ORM\OneToMany(targetEntity="Tab", mappedBy="workspace", cascade={"persist"})
    */
  private $tabs;
  
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tabs = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set usuario
     *
     * @param \HootSuite\BackofficeBundle\Entity\Usuario $usuario
     * @return Workspace
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

    /**
     * Add tabs
     *
     * @param \HootSuite\BackofficeBundle\Entity\Tab $tabs
     * @return Workspace
     */
    public function addTab(\HootSuite\BackofficeBundle\Entity\Tab $tabs)
    {
        $this->tabs[] = $tabs;

        return $this;
    }

    /**
     * Remove tabs
     *
     * @param \HootSuite\BackofficeBundle\Entity\Tab $tabs
     */
    public function removeTab(\HootSuite\BackofficeBundle\Entity\Tab $tabs)
    {
        $this->tabs->removeElement($tabs);
    }

    /**
     * Get tabs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTabs()
    {
        return $this->tabs;
    }
}
