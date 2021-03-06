<?php

namespace HootSuite\BackofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Table(name="notification")
 * @ORM\Entity()
 */
class Notification
{
  /**
   * @var integer $id
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;
    
  /**
    * @ORM\OneToOne(targetEntity="Usuario", inversedBy="notification" )
    */
  private $usuario;
  
  /** 
    * @var boolean $general_sound
    * @ORM\Column(type="boolean")
    */
  protected $general_sound;
  
  /** 
    * @var boolean $sound_like_post
    * @ORM\Column(type="boolean")
    */
  protected $sound_like_post;
  
  /** 
    * @var boolean $message_like_post
    * @ORM\Column(type="boolean")
    */
  protected $message_like_post;
  
  /** 
    * @var boolean $sount_coment_post
    * @ORM\Column(type="boolean")
    */
  protected $sount_coment_post;
  
  /** 
    * @var boolean $message_coment_post
    * @ORM\Column(type="boolean")
    */
  protected $message_coment_post;
  
  /** 
    * @var boolean $sound_coment_other
    * @ORM\Column(type="boolean")
    */
  protected $sound_coment_other;
  
  /** 
    * @var boolean $message_coment_other
    * @ORM\Column(type="boolean")
    */
  protected $message_coment_other;
  
  /** 
    * @var boolean $message_diary_sumary
    * @ORM\Column(type="boolean")
    */
  protected $message_diary_sumary;
  
  /** 
    * @var boolean $message_aprovation_required
    * @ORM\Column(type="boolean")
    */
  protected $message_aprovation_required;
  

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
     * Set general_sound
     *
     * @param boolean $generalSound
     * @return Notification
     */
    public function setGeneralSound($generalSound)
    {
        $this->general_sound = $generalSound;

        return $this;
    }

    /**
     * Get general_sound
     *
     * @return boolean 
     */
    public function getGeneralSound()
    {
        return $this->general_sound;
    }

    /**
     * Set sound_like_post
     *
     * @param boolean $soundLikePost
     * @return Notification
     */
    public function setSoundLikePost($soundLikePost)
    {
        $this->sound_like_post = $soundLikePost;

        return $this;
    }

    /**
     * Get sound_like_post
     *
     * @return boolean 
     */
    public function getSoundLikePost()
    {
        return $this->sound_like_post;
    }

    /**
     * Set message_like_post
     *
     * @param boolean $messageLikePost
     * @return Notification
     */
    public function setMessageLikePost($messageLikePost)
    {
        $this->message_like_post = $messageLikePost;

        return $this;
    }

    /**
     * Get message_like_post
     *
     * @return boolean 
     */
    public function getMessageLikePost()
    {
        return $this->message_like_post;
    }

    /**
     * Set sount_coment_post
     *
     * @param boolean $sountComentPost
     * @return Notification
     */
    public function setSountComentPost($sountComentPost)
    {
        $this->sount_coment_post = $sountComentPost;

        return $this;
    }

    /**
     * Get sount_coment_post
     *
     * @return boolean 
     */
    public function getSountComentPost()
    {
        return $this->sount_coment_post;
    }

    /**
     * Set message_coment_post
     *
     * @param boolean $messageComentPost
     * @return Notification
     */
    public function setMessageComentPost($messageComentPost)
    {
        $this->message_coment_post = $messageComentPost;

        return $this;
    }

    /**
     * Get message_coment_post
     *
     * @return boolean 
     */
    public function getMessageComentPost()
    {
        return $this->message_coment_post;
    }

    /**
     * Set sound_coment_other
     *
     * @param boolean $soundComentOther
     * @return Notification
     */
    public function setSoundComentOther($soundComentOther)
    {
        $this->sound_coment_other = $soundComentOther;

        return $this;
    }

    /**
     * Get sound_coment_other
     *
     * @return boolean 
     */
    public function getSoundComentOther()
    {
        return $this->sound_coment_other;
    }

    /**
     * Set message_coment_other
     *
     * @param boolean $messageComentOther
     * @return Notification
     */
    public function setMessageComentOther($messageComentOther)
    {
        $this->message_coment_other = $messageComentOther;

        return $this;
    }

    /**
     * Get message_coment_other
     *
     * @return boolean 
     */
    public function getMessageComentOther()
    {
        return $this->message_coment_other;
    }

    /**
     * Set message_diary_sumary
     *
     * @param boolean $messageDiarySumary
     * @return Notification
     */
    public function setMessageDiarySumary($messageDiarySumary)
    {
        $this->message_diary_sumary = $messageDiarySumary;

        return $this;
    }

    /**
     * Get message_diary_sumary
     *
     * @return boolean 
     */
    public function getMessageDiarySumary()
    {
        return $this->message_diary_sumary;
    }

    /**
     * Set message_aprovation_required
     *
     * @param boolean $messageAprovationRequired
     * @return Notification
     */
    public function setMessageAprovationRequired($messageAprovationRequired)
    {
        $this->message_aprovation_required = $messageAprovationRequired;

        return $this;
    }

    /**
     * Get message_aprovation_required
     *
     * @return boolean 
     */
    public function getMessageAprovationRequired()
    {
        return $this->message_aprovation_required;
    }

    /**
     * Set usuario
     *
     * @param \HootSuite\BackofficeBundle\Entity\Usuario $usuario
     * @return Notification
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
