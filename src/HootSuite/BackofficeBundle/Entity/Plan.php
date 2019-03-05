<?php

namespace HootSuite\BackofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Table(name="plan")
 * @ORM\Entity()
 */
class Plan
{
  /**
   * @var integer $id
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;
  
  /**
    * @ORM\OneToMany(targetEntity="Usuario", mappedBy="plan")
    */
  private $usuario;
  
  /**
    * @ORM\OneToMany(targetEntity="PlanItems", mappedBy="plan")
    */
  private $items;
    
  /** 
    * @var string $name
    * @ORM\Column(type="string", length=255)
    */
  protected $name;
    
  /** 
    * @var float $price
    * @ORM\Column(type="float")
    */
  protected $price;
    
  
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->items = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Plan
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return Plan
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set usuario
     *
     * @param \HootSuite\BackofficeBundle\Entity\Usuario $usuario
     * @return Plan
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
     * Add items
     *
     * @param \HootSuite\BackofficeBundle\Entity\PlanItems $items
     * @return Plan
     */
    public function addItem(\HootSuite\BackofficeBundle\Entity\PlanItems $items)
    {
        $this->items[] = $items;

        return $this;
    }

    /**
     * Remove items
     *
     * @param \HootSuite\BackofficeBundle\Entity\PlanItems $items
     */
    public function removeItem(\HootSuite\BackofficeBundle\Entity\PlanItems $items)
    {
        $this->items->removeElement($items);
    }

    /**
     * Get items
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Add usuario
     *
     * @param \HootSuite\BackofficeBundle\Entity\Usuario $usuario
     * @return Plan
     */
    public function addUsuario(\HootSuite\BackofficeBundle\Entity\Usuario $usuario)
    {
        $this->usuario[] = $usuario;

        return $this;
    }

    /**
     * Remove usuario
     *
     * @param \HootSuite\BackofficeBundle\Entity\Usuario $usuario
     */
    public function removeUsuario(\HootSuite\BackofficeBundle\Entity\Usuario $usuario)
    {
        $this->usuario->removeElement($usuario);
    }
}
