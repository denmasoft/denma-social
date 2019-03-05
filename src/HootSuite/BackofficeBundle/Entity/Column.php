<?php

namespace HootSuite\BackofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Table(name="columna")
 * @ORM\Entity()
 */
class Column
{
  /**
   * @var integer $id
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;
    
  /**
    * @ORM\ManyToOne(targetEntity="SocialNetwork", inversedBy="columns")
    * @ORM\JoinColumn(name="social_network_id", referencedColumnName="id")
    */
  private $social_network;
  
  /**
    * @ORM\OneToMany(targetEntity="TabColumn", mappedBy="column")
    */
  private $tabs;

  /**
   * @var string $name
   * @ORM\Column( type="string", length=50)
   * @Assert\NotBlank()
   */
  private $name;

  /**
   * @var string $description
   * @ORM\Column( type="string", length=255)
   * @Assert\NotBlank()
   */
  private $description;

  /**
   * @var string $api
   * @ORM\Column( type="string", length=255)
   * @Assert\NotBlank()
   */
  private $api;

  /**
   * @var string $glyphicon
   * @ORM\Column( type="string", length=100, nullable=true )
   * @Assert\NotBlank()
   */
  private $glyphicon;

  /**
   * @var string $glyphicon
   * @ORM\Column( type="string", length=100, nullable=true )
   * @Assert\NotBlank()
   */
  private $filter;

  /**
   * @var string $template
   * @ORM\Column( type="string", length=255 )
   */
  private $template;
  
  /**
   * @var string $type
   * @ORM\Column( type="string", length=20)
   * @Assert\NotBlank()
   */
  private $type;
  
  /**
   * @var integer $position
   * @ORM\Column( type="integer" )
   */
  private $position;
  
  /**
   * @var integer $count
   * @ORM\Column( type="integer", nullable=true )
   */
  private $count;
  
  /**
   * @var integer $visible
   * @ORM\Column( type="boolean" )
   */
  private $visible;
  
  /**
   * @var boolean $user_id
   * @ORM\Column( type="boolean" )
   */
  private $user_id;

  
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
     * Set name
     *
     * @param string $name
     * @return Column
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
     * Set api
     *
     * @param string $api
     * @return Column
     */
    public function setApi($api)
    {
        $this->api = $api;

        return $this;
    }

    /**
     * Get api
     *
     * @return string 
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * Set glyphicon
     *
     * @param string $glyphicon
     * @return Column
     */
    public function setGlyphicon($glyphicon)
    {
        $this->glyphicon = $glyphicon;

        return $this;
    }

    /**
     * Get glyphicon
     *
     * @return string 
     */
    public function getGlyphicon()
    {
        return $this->glyphicon;
    }

    /**
     * Set filter
     *
     * @param string $filter
     * @return Column
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Get filter
     *
     * @return string 
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Add tabs
     *
     * @param \HootSuite\BackofficeBundle\Entity\TabColumn $tabs
     * @return Column
     */
    public function addTab(\HootSuite\BackofficeBundle\Entity\TabColumn $tabs)
    {
        $this->tabs[] = $tabs;

        return $this;
    }

    /**
     * Remove tabs
     *
     * @param \HootSuite\BackofficeBundle\Entity\TabColumn $tabs
     */
    public function removeTab(\HootSuite\BackofficeBundle\Entity\TabColumn $tabs)
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

    /**
     * Set social_network
     *
     * @param \HootSuite\BackofficeBundle\Entity\SocialNetwork $socialNetwork
     * @return Column
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

    /**
     * Set template
     *
     * @param string $template
     * @return Column
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return string 
     */
    public function getTemplate()
    {
        return $this->template;
    }
    
    /**
     * Set type
     *
     * @param string $type
     * @return TabColumn
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return Column
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set count
     *
     * @param integer $count
     * @return Column
     */
    public function setCount($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * Get count
     *
     * @return integer 
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Set user_id
     *
     * @param boolean $userId
     * @return Column
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;

        return $this;
    }

    /**
     * Get user_id
     *
     * @return boolean 
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set visible
     *
     * @param boolean $visible
     * @return Column
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
    
        return $this;
    }

    /**
     * Get visible
     *
     * @return boolean 
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Column
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
