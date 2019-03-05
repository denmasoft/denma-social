<?php

namespace HootSuite\BackofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Table(name="tab_column")
 * @ORM\Entity(repositoryClass="TabColumnRepository")
 */
class TabColumn
{
  /**
   * @var integer $id
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;
    
  /**
    * @ORM\ManyToOne(targetEntity="Tab", inversedBy="columns")
    * @ORM\JoinColumn(name="tab_id", referencedColumnName="id")
    */
  private $tab;
    
  /**
    * @ORM\ManyToOne(targetEntity="Column", inversedBy="tabs")
    * @ORM\JoinColumn(name="column_id", referencedColumnName="id")
    */
  private $column;
  
  /**
   * @var integer $position
   * @ORM\Column( type="integer" )
   */
  private $position;
    
  /**
    * @ORM\ManyToOne(targetEntity="ProfilesUsuario", inversedBy="tab_columns")
    * @ORM\JoinColumn(name="profile_usuario_id", referencedColumnName="id")
    */
  private $profile_usuario;
  
  /**
    * @ORM\OneToMany(targetEntity="TabColumnTerms", mappedBy="tab_column", cascade={"persist","remove"})
    */
  private $terms;


  /**
   * @var string $name
   * @ORM\Column( type="string", length=50)
   * @Assert\NotBlank()
   */
  private $name;

 

  
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->terms = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return TabColumn
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
     * Set tab
     *
     * @param \HootSuite\BackofficeBundle\Entity\Tab $tab
     * @return TabColumn
     */
    public function setTab(\HootSuite\BackofficeBundle\Entity\Tab $tab = null)
    {
        $this->tab = $tab;

        return $this;
    }

    /**
     * Get tab
     *
     * @return \HootSuite\BackofficeBundle\Entity\Tab 
     */
    public function getTab()
    {
        return $this->tab;
    }

    /**
     * Set column
     *
     * @param \HootSuite\BackofficeBundle\Entity\Column $column
     * @return TabColumn
     */
    public function setColumn(\HootSuite\BackofficeBundle\Entity\Column $column = null)
    {
        $this->column = $column;

        return $this;
    }

    /**
     * Get column
     *
     * @return \HootSuite\BackofficeBundle\Entity\Column 
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Add terms
     *
     * @param \HootSuite\BackofficeBundle\Entity\TabColumnTerms $terms
     * @return TabColumn
     */
    public function addTerm(\HootSuite\BackofficeBundle\Entity\TabColumnTerms $terms)
    {
        $this->terms[] = $terms;

        return $this;
    }

    /**
     * Remove terms
     *
     * @param \HootSuite\BackofficeBundle\Entity\TabColumnTerms $terms
     */
    public function removeTerm(\HootSuite\BackofficeBundle\Entity\TabColumnTerms $terms)
    {
        $this->terms->removeElement($terms);
    }

    /**
     * Get terms
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTerms()
    {
        return $this->terms;
    }

    /**
     * Set profile_usuario
     *
     * @param \HootSuite\BackofficeBundle\Entity\ProfilesUsuario $profileUsuario
     * @return TabColumn
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
}
