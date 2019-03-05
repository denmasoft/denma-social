<?php

namespace HootSuite\BackofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Table(name="profiles_usuario")
 * @ORM\Entity(repositoryClass="ProfilesUsuarioRepository")
 */
class ProfilesUsuario
{
  /**
   * @var integer $id
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;
    
  /**
    * @ORM\ManyToOne(targetEntity="Usuario", inversedBy="profiles" )
    */
  private $usuario;
  
   /**
   * @ORM\ManyToMany(targetEntity="Message", inversedBy="profiles_usuario")
   * @ORM\JoinTable(name="profiles_usuario_message",
   *   joinColumns={
   *     @ORM\JoinColumn(name="profiles_usuario_id", referencedColumnName="id")
   *   },
   *   inverseJoinColumns={
   *     @ORM\JoinColumn(name="message_id", referencedColumnName="id")
   *   }
   * )
   */  
    private $messages; 
    
  /**
    * @ORM\ManyToOne(targetEntity="SocialNetwork", inversedBy="profiles" )
    */
  private $social_network;
  
   /**
    * @ORM\OneToMany(targetEntity="TabColumn", mappedBy="profile_usuario", cascade={"persist","remove"})
    */
  private $tab_columns;
  
   /**
    * @ORM\OneToOne(targetEntity="Groups", mappedBy="profile_usuario", cascade={"persist","remove"})
    */
  private $group;
  
   /*/**
    * @ORM\OneToMany(targetEntity="Organization", mappedBy="profile_usuario", cascade={"persist","remove"})
    */
  //private $organizations;
  
  /**
   * @var string $full_name
   * @ORM\Column( type="string", length=150)
   * @Assert\NotBlank()
   */
  private $full_name;
  
  /**
   * @var string $username
   * @ORM\Column( type="string", length=100)
   * @Assert\NotBlank()
   */
  private $username;
  
  /**
   * @var string $userid
   * @ORM\Column( type="string", length=255)
   * @Assert\NotBlank()
   */
  private $userid;
  
  /**
   * @var string $token
   * @ORM\Column( type="string", length=255)
   * @Assert\NotBlank()
   */
  private $token;
  
  /**
   * @var string $token_secret
   * @ORM\Column( type="string", length=255)
   * @Assert\NotBlank()
   */
  private $token_secret;
  
  /**
   * @var string $avatar
   * @ORM\Column( type="string", length=255)
   * @Assert\NotBlank()
   */
  private $avatar;
  
  /**
   * @var datetime $created_at
   * @ORM\Column( type="datetime")
   * @Assert\NotBlank()
   */
  private $created_at;
  
  /**
   * @var boolean $favorite
   * @ORM\Column( type="boolean")
   * @Assert\NotBlank()
   */
  private $favorite;
  
  /**
   * @var boolean $selected
   * @ORM\Column( type="boolean")
   * @Assert\NotBlank()
   */
  private $selected;
  
  /**
   * @var string $url
   * @ORM\Column( type="string", length=255 )
   * @Assert\NotBlank()
   */
  private $url;
    
  /**
   * @var string $site
   * @ORM\Column( type="string", length=255 )
   * @Assert\NotBlank()
   */
  private $site;
   

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
     * Set full_name
     *
     * @param string $fullName
     * @return ProfilesUsuario
     */
    public function setFullName($fullName)
    {
        $this->full_name = $fullName;

        return $this;
    }

    /**
     * Get full_name
     *
     * @return string 
     */
    public function getFullName()
    {
        return $this->full_name;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return ProfilesUsuario
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return ProfilesUsuario
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set tokenSecret
     *
     * @param string $tokenSecret
     * @return ProfilesUsuario
     */
    public function setTokenSecret($tokenSecret)
    {
        $this->token_secret = $tokenSecret;

        return $this;
    }

    /**
     * Get tokenSecret
     *
     * @return string 
     */
    public function getTokenSecret()
    {
        return $this->token_secret;
    }

    /**
     * Set avatar
     *
     * @param string $avatar
     * @return ProfilesUsuario
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return string 
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return ProfilesUsuario
     */
    public function setCreatedAt($createdAt = null)
    {
        $this->created_at = new \Datetime('now');

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
     * Set favorite
     *
     * @param boolean $favorite
     * @return ProfilesUsuario
     */
    public function setFavorite($favorite)
    {
        $this->favorite = $favorite;

        return $this;
    }

    /**
     * Get favorite
     *
     * @return boolean 
     */
    public function getFavorite()
    {
        return $this->favorite;
    }

    /**
     * Set selected
     *
     * @param boolean $selected
     * @return ProfilesUsuario
     */
    public function setSelected($selected)
    {
        $this->selected = $selected;

        return $this;
    }

    /**
     * Get selected
     *
     * @return boolean 
     */
    public function getSelected()
    {
        return $this->selected;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return ProfilesUsuario
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set usuario
     *
     * @param \HootSuite\BackofficeBundle\Entity\Usuario $usuario
     * @return ProfilesUsuario
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
     * Set social_network
     *
     * @param \HootSuite\BackofficeBundle\Entity\SocialNetwork $socialNetwork
     * @return ProfilesUsuario
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

   /* /**
     * Add organizations
     *
     * @param \HootSuite\BackofficeBundle\Entity\Organization $organizations
     * @return ProfilesUsuario
     */
    /*public function addOrganization(\HootSuite\BackofficeBundle\Entity\Organization $organizations)
    {
        $this->organizations[] = $organizations;

        return $this;
    }

    /**
     * Remove organizations
     *
     * @param \HootSuite\BackofficeBundle\Entity\Organization $organizations
     */
    /*public function removeOrganization(\HootSuite\BackofficeBundle\Entity\Organization $organizations)
    {
        $this->organizations->removeElement($organizations);
    }

    /**
     * Get organizations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    /*public function getOrganizations()
    {
        return $this->organizations;
    }

    /**
     * Set userid
     *
     * @param string $userid
     * @return ProfilesUsuario
     */
    public function setUserid($userid)
    {
        $this->userid = $userid;

        return $this;
    }

    /**
     * Get userid
     *
     * @return string 
     */
    public function getUserid()
    {
        return $this->userid;
    }

    /**
     * Add tab_columns
     *
     * @param \HootSuite\BackofficeBundle\Entity\TabColumn $tabColumns
     * @return ProfilesUsuario
     */
    public function addTabColumn(\HootSuite\BackofficeBundle\Entity\TabColumn $tabColumns)
    {
        $this->tab_columns[] = $tabColumns;

        return $this;
    }

    /**
     * Remove tab_columns
     *
     * @param \HootSuite\BackofficeBundle\Entity\TabColumn $tabColumns
     */
    public function removeTabColumn(\HootSuite\BackofficeBundle\Entity\TabColumn $tabColumns)
    {
        $this->tab_columns->removeElement($tabColumns);
    }

    /**
     * Get tab_columns
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTabColumns()
    {
        return $this->tab_columns;
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->messages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tab_columns = new \Doctrine\Common\Collections\ArrayCollection();
       // $this->organizations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add messages
     *
     * @param \HootSuite\BackofficeBundle\Entity\Message $messages
     * @return ProfilesUsuario
     */
    public function addMessage(\HootSuite\BackofficeBundle\Entity\Message $messages)
    {
        $this->messages[] = $messages;
    
        return $this;
    }

    /**
     * Remove messages
     *
     * @param \HootSuite\BackofficeBundle\Entity\Message $messages
     */
    public function removeMessage(\HootSuite\BackofficeBundle\Entity\Message $messages)
    {
        $this->messages->removeElement($messages);
    }

    /**
     * Get messages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMessages()
    {
        return $this->messages;
    }


    /**
     * Set group
     *
     * @param \HootSuite\BackofficeBundle\Entity\Groups $group
     * @return ProfilesUsuario
     */
    public function setGroup(\HootSuite\BackofficeBundle\Entity\Groups $group = null)
    {
        $this->group = $group;
    
        return $this;
    }

    /**
     * Get group
     *
     * @return \HootSuite\BackofficeBundle\Entity\Groups 
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set site
     *
     * @param string $site
     * @return ProfilesUsuario
     */
    public function setSite($site)
    {
        $this->site = $site;
    
        return $this;
    }

    /**
     * Get site
     *
     * @return string 
     */
    public function getSite()
    {
        return $this->site;
    }
}
