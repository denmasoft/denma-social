<?php

namespace HootSuite\BackofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Table(name="member")
 * @ORM\Entity()
 */
class Member
{
  /**
   * @var integer $id
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;
    
  /**
    * @ORM\ManyToOne(targetEntity="Organization", inversedBy="members" )
    */
  private $organization;

    /**
     * @ORM\ManyToOne(targetEntity="Usuario", inversedBy="profiles" )
     */
    private $usuario;

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
     * Set organization
     *
     * @param \HootSuite\BackofficeBundle\Entity\Organization $organization
     * @return Member
     */
    public function setOrganization(\HootSuite\BackofficeBundle\Entity\Organization $organization = null)
    {
        $this->organization = $organization;
        return $this;
    }

    /**
     * Get organization
     *
     * @return \HootSuite\BackofficeBundle\Entity\Organization 
     */
    public function getOrganization()
    {
        return $this->organization;
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
}
