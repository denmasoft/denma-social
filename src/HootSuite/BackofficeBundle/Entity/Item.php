<?php

namespace HootSuite\BackofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Table(name="item")
 * @ORM\Entity()
 */
class Item
{
  /**
   * @var integer $id
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;
  
  /**
    * @ORM\OneToMany(targetEntity="PlanItems", mappedBy="item")
    */
  private $plans;
    
  /** 
    * @var string $name
    * @ORM\Column(type="string", length=255)
    */
  protected $name;
  
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->plans = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Item
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
     * Add plans
     *
     * @param \HootSuite\BackofficeBundle\Entity\PlanItems $plans
     * @return Item
     */
    public function addPlan(\HootSuite\BackofficeBundle\Entity\PlanItems $plans)
    {
        $this->plans[] = $plans;

        return $this;
    }

    /**
     * Remove plans
     *
     * @param \HootSuite\BackofficeBundle\Entity\PlanItems $plans
     */
    public function removePlan(\HootSuite\BackofficeBundle\Entity\PlanItems $plans)
    {
        $this->plans->removeElement($plans);
    }

    /**
     * Get plans
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPlans()
    {
        return $this->plans;
    }
}
