<?php

namespace HootSuite\BackofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Table(name="social_list")
 * @ORM\Entity()
 */
class SocialList
{
  /**
   * @var integer $id
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;
    
  /**
    * @ORM\ManyToOne(targetEntity="SocialNetwork", inversedBy="lists")
    * @ORM\JoinColumn(name="social_network_id", referencedColumnName="id")
    */
  private $social_network;
  
  /** 
    * @var string $name
    * @ORM\Column(type="string", length=100)
    */
  protected $name;
  

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
     * @return SocialList
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
     * Set social_network
     *
     * @param \HootSuite\BackofficeBundle\Entity\SocialNetwork $socialNetwork
     * @return SocialList
     */
    public function setSocialNetwork(\HootSuite\BackofficeBundle\Entity\SocialNetwork $socialNetwork = null)
    {
        $this->social_network = $socialNetwork;

        return $this;
    }

    /**
     * Get social_network
     *
     * @return \HootSuite\BackofficeBundle\Entity\SocialNetwork 
     */
    public function getSocialNetwork()
    {
        return $this->social_network;
    }
}
