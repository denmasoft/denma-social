<?php

namespace HootSuite\BackofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Table(name="usuario")
 * @ORM\Entity(repositoryClass="UsuarioRepository")
 * @DoctrineAssert\UniqueEntity("email")
 */
class Usuario implements AdvancedUserInterface, \Serializable
{
  /**
   * @var integer $id
   * @ORM\Column(name="id", type="integer", nullable=false)
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;
  
   /**
    * @ORM\OneToMany(targetEntity="File", mappedBy="usuario", cascade={"persist","remove"})
    */
  private $files;
  
   /**
    * @ORM\OneToMany(targetEntity="RssAtom", mappedBy="usuario", cascade={"persist","remove"})
    */
  private $rss;
  
   /**
    * @ORM\OneToMany(targetEntity="ProfilesUsuario", mappedBy="usuario", cascade={"persist","remove"})
    */
  private $profiles;
  
  /** 
   * @ORM\OneToOne(targetEntity="InvoiceData", mappedBy="usuario")
   */
  protected $invoice_data;
  
  /** 
   * @ORM\OneToOne(targetEntity="Workspace", mappedBy="usuario", cascade={"persist","remove"})
   */
  protected $workspace;
  
  /** 
   * @ORM\OneToOne(targetEntity="Notification", mappedBy="usuario")
   */
  protected $notification;
  
  /** 
   * @ORM\OneToOne(targetEntity="AutomaticProgram", mappedBy="usuario")
   */
  protected $automatic_program;
  
  
  /**
    * @ORM\ManyToOne(targetEntity="Plan", inversedBy="usuario")
    */
  protected $plan;
  
  /**
   * @var string $name
   * @ORM\Column( type="string", length=150)
   * @Assert\NotBlank()
   */
  private $name;
  
  /**
   * @var string $initials
   * @ORM\Column( type="string", length=20, nullable=true)
   */
  private $initials;
  
  /**
   * @var string $phone
   * @ORM\Column( type="string", length=30, nullable=true)
   */
  private $phone;

  /**
   * @var string $email
   * @ORM\Column( type="string", length=100, unique=true)
   * @Assert\Email()
   * @Assert\NotBlank()
   */
  private $email;
  
  /**
   * @var string $image
   * @ORM\Column( type="string", length=255, nullable=true)
   */
  private $image;
  
  /**
   * @var string $company
   * @ORM\Column( type="string", length=100, nullable=true)
   */
  private $company;
  
  /**
   * @var string $charge
   * @ORM\Column( type="string", length=100, nullable=true)
   */
  private $charge;
  
  /**
   * @var string $description
   * @ORM\Column( type="text", nullable=true)
   */
  private $description;
  
  /**
   * @var string $time_zone
   * @ORM\Column( type="string", length=100, nullable=true)
   */
  private $time_zone;
  
  /**
   * @var string $password
   * @ORM\Column(type="string", length=255)
   * @Assert\Length(min = "8")
   */
  private $password;
  
  /**
   * @var string $salt
   * @ORM\Column(type="string", length=255, nullable=true)
   */
  private $salt; 
  
  /**
   * @var date $created_at
   * @ORM\Column(type="datetime", nullable=true)
   */    
  private $created_at; 
  
  /**
   * @var date $active
   * @ORM\Column(type="boolean")
   */    
  private $active;
  
   /**
    * Random string sent to the user email address in order to verify it
    * @ORM\Column(type="string", length=255, nullable=true) 
    */
    private $confirmation_token;

   /**
    * @ORM\Column(type="datetime", nullable=true) 
    */
    private $password_requested_at;
  
  /**
   * Set password
   * @param string $password
   * @return Asociations
   */
  public function setPassword($password)
  {
      $this->password = $password;

      return $this;
  }

  /**
   * Get password
   * @return string 
   */
  public function getPassword()
  {
      return $this->password;
  }

  /**
   * Set salt
   * @param string $salt
   * @return Asociations
   */
  public function setSalt($saltp=null)
  {
    if($saltp==null)
    {
      $time = new \DateTime('now');
      $seg = (string)$time->format('s');
      $this->salt = md5(uniqid().$seg);    
      return $this;
    }
    else
    {
      $this->salt = $saltp;
      return $this;
    }
  }

  /**
   * Get salt
   * @return string 
   */
  public function getSalt()
  {
      return $this->salt;
  }  
  
  //Métodos que implementa UserInterface    
  public function eraseCredentials()
  {

  }  
  
  public function getRoles()
  {
      return array("ROLE_USUARIO");
  }
  
  public function getUsername()
  {
      return $this->email;
  } 
  
  public function equals(AdvancedUserInterface $user)
  {
      return $this->getEmail()==$user->getUsername();
  } 

  public function isAccountNonExpired()
  {
      return true;
  }

  public function isAccountNonLocked()
  {
      return true;
  }

  public function isCredentialsNonExpired()
  {
      return true;
  }

  public function isEnabled()
  {
      if( !$this->active ){
          return false;
      }
      return $this->active;
  }
  
  /*--------------------------------*/
 
  public function __toString()
  {
      return (string)$this->email;
  }  
  
       /**
   * @see \Serializable::serialize()
   */
  public function serialize()
  {
      return serialize(array(
          $this->id,
      ));
  }

  /**
   * @see \Serializable::unserialize()
   */
  public function unserialize($serialized)
  {
      list (
          $this->id,
      ) = unserialize($serialized);
  }
 

    /**
     * Set fecha_registro
     *
     * @param \DateTime $fechaRegistro
     * @return Usuario
     */
    public function setCreatedAt($fechaRegistro = NULL)
    {
        $this->created_at = new \Datetime('now');
    
        return $this;
    }

    /**
     * Get fecha_registro
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set confirmation_token
     *
     * @param string $confirmation_token
     * @return Customer
     */
    public function setConfirmationToken($confirmation_token = null)
    {
        if( $confirmation_token === null )
        {
          $time = new \DateTime('now');
          $seg = (string)$time->format('s');
          $this->confirmation_token = md5(uniqid().$seg);
        }
        else
        {
          $this->confirmation_token = $confirmation_token;
        }
        return $this;
    }

    /**
     * Get confirmation_token
     *
     * @return string 
     */
    public function getConfirmationToken()
    {
        return $this->confirmation_token;
    }
        
    /**
     * Get password_requested_at
     *
     * @return \DateTime 
     */
    public function getPasswordRequestedAt()
    {
        return $this->password_requested_at;
    }

    /**
     * Set password_requested_at
     *
     * @param \DateTime $password_requested_at
     * @return Customer
     */
    public function setPasswordRequestedAt()
    {
        $this->password_requested_at = new \Datetime('now');
    
        return $this;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->files = new \Doctrine\Common\Collections\ArrayCollection();
        $this->rss = new \Doctrine\Common\Collections\ArrayCollection();
        $this->profiles = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Usuario
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
     * Set initials
     *
     * @param string $initials
     * @return Usuario
     */
    public function setInitials($initials)
    {
        $this->initials = $initials;

        return $this;
    }

    /**
     * Get initials
     *
     * @return string 
     */
    public function getInitials()
    {
        return $this->initials;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Usuario
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Usuario
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return Usuario
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set company
     *
     * @param string $company
     * @return Usuario
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string 
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set charge
     *
     * @param string $charge
     * @return Usuario
     */
    public function setCharge($charge)
    {
        $this->charge = $charge;

        return $this;
    }

    /**
     * Get charge
     *
     * @return string 
     */
    public function getCharge()
    {
        return $this->charge;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Usuario
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

    /**
     * Set time_zone
     *
     * @param string $timeZone
     * @return Usuario
     */
    public function setTimeZone($timeZone)
    {
        $this->time_zone = $timeZone;

        return $this;
    }

    /**
     * Get time_zone
     *
     * @return string 
     */
    public function getTimeZone()
    {
        return $this->time_zone;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Usuario
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Add files
     *
     * @param \HootSuite\BackofficeBundle\Entity\File $files
     * @return Usuario
     */
    public function addFile(\HootSuite\BackofficeBundle\Entity\File $files)
    {
        $this->files[] = $files;

        return $this;
    }

    /**
     * Remove files
     *
     * @param \HootSuite\BackofficeBundle\Entity\File $files
     */
    public function removeFile(\HootSuite\BackofficeBundle\Entity\File $files)
    {
        $this->files->removeElement($files);
    }

    /**
     * Get files
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Add rss
     *
     * @param \HootSuite\BackofficeBundle\Entity\RssAtom $rss
     * @return Usuario
     */
    public function addRss(\HootSuite\BackofficeBundle\Entity\RssAtom $rss)
    {
        $this->rss[] = $rss;

        return $this;
    }

    /**
     * Remove rss
     *
     * @param \HootSuite\BackofficeBundle\Entity\RssAtom $rss
     */
    public function removeRss(\HootSuite\BackofficeBundle\Entity\RssAtom $rss)
    {
        $this->rss->removeElement($rss);
    }

    /**
     * Get rss
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRss()
    {
        return $this->rss;
    }

    /**
     * Add profiles
     *
     * @param \HootSuite\BackofficeBundle\Entity\ProfilesUsuario $profiles
     * @return Usuario
     */
    public function addProfile(\HootSuite\BackofficeBundle\Entity\ProfilesUsuario $profiles)
    {
        $this->profiles[] = $profiles;

        return $this;
    }

    /**
     * Remove profiles
     *
     * @param \HootSuite\BackofficeBundle\Entity\ProfilesUsuario $profiles
     */
    public function removeProfile(\HootSuite\BackofficeBundle\Entity\ProfilesUsuario $profiles)
    {
        $this->profiles->removeElement($profiles);
    }

    /**
     * Get profiles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProfiles()
    {
        return $this->profiles;
    }

    /**
     * Set invoice_data
     *
     * @param \HootSuite\BackofficeBundle\Entity\InvoiceData $invoiceData
     * @return Usuario
     */
    public function setInvoiceData(\HootSuite\BackofficeBundle\Entity\InvoiceData $invoiceData = null)
    {
        $this->invoice_data = $invoiceData;

        return $this;
    }

    /**
     * Get invoice_data
     *
     * @return \HootSuite\BackofficeBundle\Entity\InvoiceData 
     */
    public function getInvoiceData()
    {
        return $this->invoice_data;
    }

    /**
     * Set workspace
     *
     * @param \HootSuite\BackofficeBundle\Entity\Workspace $workspace
     * @return Usuario
     */
    public function setWorkspace(\HootSuite\BackofficeBundle\Entity\Workspace $workspace = null)
    {
        $this->workspace = $workspace;

        return $this;
    }

    /**
     * Get workspace
     *
     * @return \HootSuite\BackofficeBundle\Entity\Workspace 
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * Set notification
     *
     * @param \HootSuite\BackofficeBundle\Entity\Notification $notification
     * @return Usuario
     */
    public function setNotification(\HootSuite\BackofficeBundle\Entity\Notification $notification = null)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * Get notification
     *
     * @return \HootSuite\BackofficeBundle\Entity\Notification 
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Set automatic_program
     *
     * @param \HootSuite\BackofficeBundle\Entity\AutomaticProgram $automaticProgram
     * @return Usuario
     */
    public function setAutomaticProgram(\HootSuite\BackofficeBundle\Entity\AutomaticProgram $automaticProgram = null)
    {
        $this->automatic_program = $automaticProgram;

        return $this;
    }

    /**
     * Get automatic_program
     *
     * @return \HootSuite\BackofficeBundle\Entity\AutomaticProgram 
     */
    public function getAutomaticProgram()
    {
        return $this->automatic_program;
    }

    /**
     * Set plan
     *
     * @param \HootSuite\BackofficeBundle\Entity\Plan $plan
     * @return Usuario
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
}
