<?php

namespace HootSuite\BackofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Table(name="tab")
 * @ORM\Entity(repositoryClass="TabRepository")
 */
class Tab
{
  /**
   * @var integer $id
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;
    
  /**
    * @ORM\OneToMany(targetEntity="TabColumn", mappedBy="tab")
    */
  private $columns;
  
    /**
    * @ORM\ManyToOne(targetEntity="Workspace", inversedBy="tabs", cascade={"persist"} )
    */
  private $workspace;

  /**
   * @var string $name
   * @ORM\Column( type="string", length=50)
   * @Assert\NotBlank()
   */
  private $name;

  /**
   * @var boolean $active
   * @ORM\Column( type="boolean" )
   * @Assert\NotBlank()
   */
  private $active;

  /**
   * @var datetime $created_at
   * @ORM\Column( type="datetime" )
   * @Assert\NotBlank()
   */
  private $created_at;
  
  /**
   * @var integer $refresh_interval
   * @ORM\Column( type="integer", length=2 )
   * @Assert\NotBlank()
   */
  private $refresh_interval;
  
  /**
   * @var integer $visible_columns
   * @ORM\Column( type="integer", length=2 )
   * @Assert\NotBlank()
   */
  private $visible_columns;
  
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->columns = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Tab
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
     * Set active
     *
     * @param boolean $active
     * @return Tab
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
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Tab
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
     * Set refresh_interval
     *
     * @param integer $refreshInterval
     * @return Tab
     */
    public function setRefreshInterval($refreshInterval)
    {
        $this->refresh_interval = $refreshInterval;

        return $this;
    }

    /**
     * Get refresh_interval
     *
     * @return integer 
     */
    public function getRefreshInterval()
    {
        return $this->refresh_interval;
    }

    /**
     * Set visible_columns
     *
     * @param integer $visibleColumns
     * @return Tab
     */
    public function setVisibleColumns($visibleColumns)
    {
        $this->visible_columns = $visibleColumns;

        return $this;
    }

    /**
     * Get visible_columns
     *
     * @return integer 
     */
    public function getVisibleColumns()
    {
        return $this->visible_columns;
    }

    /**
     * Add columns
     *
     * @param \HootSuite\BackofficeBundle\Entity\TabColumn $columns
     * @return Tab
     */
    public function addColumn(\HootSuite\BackofficeBundle\Entity\TabColumn $columns)
    {
        $this->columns[] = $columns;

        return $this;
    }

    /**
     * Remove columns
     *
     * @param \HootSuite\BackofficeBundle\Entity\TabColumn $columns
     */
    public function removeColumn(\HootSuite\BackofficeBundle\Entity\TabColumn $columns)
    {
        $this->columns->removeElement($columns);
    }

    /**
     * Get columns
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Set workspace
     *
     * @param \HootSuite\BackofficeBundle\Entity\Workspace $workspace
     * @return Tab
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
}
