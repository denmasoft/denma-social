<?php

namespace HootSuite\BackofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Table(name="groups")
 * @ORM\Entity()
 */
class Groups
{
  /**
   * @var integer $id
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;
  
  /**
    * @ORM\OneToOne(targetEntity="ProfilesUsuario", inversedBy="group")
    */
  private $profile_usuario;
    
  /** 
    * @var string $net_group_id
    * @ORM\Column(type="string", length=255)
    */
  protected $net_group_id;
    
  /** 
    * @var string $net_group_name
    * @ORM\Column(type="string", length=255)
    */
  protected $net_group_name;
  
   

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
     * Set net_group_id
     *
     * @param string $netGroupId
     * @return Groups
     */
    public function setNetGroupId($netGroupId)
    {
        $this->net_group_id = $netGroupId;
    
        return $this;
    }

    /**
     * Get net_group_id
     *
     * @return string 
     */
    public function getNetGroupId()
    {
        return $this->net_group_id;
    }

    /**
     * Set net_group_name
     *
     * @param string $netGroupName
     * @return Groups
     */
    public function setNetGroupName($netGroupName)
    {
        $this->net_group_name = $netGroupName;
    
        return $this;
    }

    /**
     * Get net_group_name
     *
     * @return string 
     */
    public function getNetGroupName()
    {
        return $this->net_group_name;
    }

    /**
     * Set profile_usuario
     *
     * @param \HootSuite\BackofficeBundle\Entity\ProfilesUsuario $profileUsuario
     * @return Groups
     */
    public function setProfileUsuario(\HootSuite\BackofficeBundle\Entity\ProfilesUsuario $profileUsuario = null)
    {
        $this->profile_usuario = $profileUsuario;
    
        return $this;
    }

    /**
     * Get profile_usuario
     *
     * @return \HootSuite\BackofficeBundle\Entity\ProfilesUsuario 
     */
    public function getProfileUsuario()
    {
        return $this->profile_usuario;
    }
}
