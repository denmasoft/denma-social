<?php

namespace HootSuite\BackofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Table(name="plan_items")
 * @ORM\Entity()
 */
class PlanItems
{
  /**
   * @var integer $id
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;
    
  /**
    * @ORM\ManyToOne(targetEntity="Plan", inversedBy="items")
    * @ORM\JoinColumn(name="plan_id", referencedColumnName="id")
    */
  private $plan;
    
  /**
    * @ORM\ManyToOne(targetEntity="Item", inversedBy="plans")
    * @ORM\JoinColumn(name="item_id", referencedColumnName="id")
    */
  private $item;

  /**
   * @var string $value
   * @ORM\Column( type="string", length=255 )
   * @Assert\NotBlank()
   */
  private $value;

 

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
     * Set value
     *
     * @param string $value
     * @return PlanItems
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set plan
     *
     * @param \HootSuite\BackofficeBundle\Entity\Plan $plan
     * @return PlanItems
     */
    public function setPlan(\HootSuite\BackofficeBundle\Entity\Plan $plan = null)
    {
        $this->plan = $plan;

        return $this;
    }

    /**
     * Get plan
     *
     * @return \HootSuite\BackofficeBundle\Entity\Plan 
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * Set item
     *
     * @param \HootSuite\BackofficeBundle\Entity\Item $item
     * @return PlanItems
     */
    public function setItem(\HootSuite\BackofficeBundle\Entity\Item $item = null)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item
     *
     * @return \HootSuite\BackofficeBundle\Entity\Item 
     */
    public function getItem()
    {
        return $this->item;
    }
}
