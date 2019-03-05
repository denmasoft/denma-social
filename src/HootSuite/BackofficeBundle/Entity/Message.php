<?php

namespace HootSuite\BackofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Table(name="message")
 * @ORM\Entity(repositoryClass="MessageRepository")
 */
class Message
{
  /**
   * @var integer $id
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;
  
  /**
   * @ORM\ManyToMany(targetEntity="ProfilesUsuario", mappedBy="messages" )
   */
  private $profiles_usuario;
  
    /**
   * @var string $avatar
   * @ORM\Column( type="string", nullable=false )
   * @Assert\NotBlank()
   */
  private $avatar;
  
    /**
   * @var text $text
   * @ORM\Column( type="text", nullable=false )
   * @Assert\NotBlank()
   */
  private $text;

    /**
   * @var string $state
   * @ORM\Column( type="string", length=1 )
   * @Assert\NotBlank()
   */
  private $state;
  
    /**
   * @var datetime $created_at
   * @ORM\Column( type="datetime" )
   * @Assert\NotBlank()
   */
  private $created_at;
  
    /**
   * @var datetime $programed
   * @ORM\Column( type="datetime", nullable=true )
   * @Assert\NotBlank()
   */
  private $programed;
  
    /**
   * @var string $ubication
   * @ORM\Column( type="string", length=255, nullable=true )
   * @Assert\NotBlank()
   */
  private $ubication;
  
    /**
   * @var string $attachment
   * @ORM\Column( type="string", length=255, nullable=true )
   * @Assert\NotBlank()
   */
  private $attachment;
  
 
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
     * Set text
     *
     * @param string $text
     * @return Message
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set state
     *
     * @param \string $state
     * @return Message
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return \string 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Message
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
     * Set programed
     *
     * @param \DateTime $programed
     * @return Message
     */
    public function setProgramed($programed)
    {
        $this->programed = $programed;

        return $this;
    }

    /**
     * Get programed
     *
     * @return \DateTime 
     */
    public function getProgramed()
    {
        return $this->programed;
    }

    /**
     * Set ubication
     *
     * @param string $ubication
     * @return Message
     */
    public function setUbication($ubication)
    {
        $this->ubication = $ubication;

        return $this;
    }

    /**
     * Get ubication
     *
     * @return string 
     */
    public function getUbication()
    {
        return $this->ubication;
    }

    /**
     * Set attachment
     *
     * @param string $attachment
     * @return Message
     */
    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;

        return $this;
    }

    /**
     * Get attachment
     *
     * @return string 
     */
    public function getAttachment()
    {
        return $this->attachment;
    }



    /**
     * Set avatar
     *
     * @param string $avatar
     * @return Message
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
     * Add profiles_usuario
     *
     * @param \HootSuite\BackofficeBundle\Entity\ProfilesUsuario $profilesUsuario
     * @return Message
     */
    public function addProfilesUsuario(\HootSuite\BackofficeBundle\Entity\ProfilesUsuario $profilesUsuario)
    {
        $this->profiles_usuario[] = $profilesUsuario;
    
        return $this;
    }

    /**
     * Remove profiles_usuario
     *
     * @param \HootSuite\BackofficeBundle\Entity\ProfilesUsuario $profilesUsuario
     */
    public function removeProfilesUsuario(\HootSuite\BackofficeBundle\Entity\ProfilesUsuario $profilesUsuario)
    {
        $this->profiles_usuario->removeElement($profilesUsuario);
    }

    /**
     * Get profiles_usuario
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProfilesUsuario()
    {
        return $this->profiles_usuario;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->profiles_usuario = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function toArray($profile){
        return array(
            'id'        => $this->id,
            'avatar'    => $this->avatar,
            'programed' => $this->programed->format("h:iA, M j, Y"),
            'text'      => $this->text,
            'created_by'=> $profile->getFullName()
        );
    }

}
