<?php

namespace HootSuite\BackofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Table(name="tab_column_terms")
 * @ORM\Entity()
 */
class TabColumnTerms
{
  /**
   * @var integer $id
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;
    
  /**
    * @ORM\ManyToOne(targetEntity="TabColumn", inversedBy="terms")
    * @ORM\JoinColumn(name="tab_column_id", referencedColumnName="id")
    */
  private $tab_column;
    
  /**
    * @var string $name
   * @ORM\Column( type="string" )
   * @Assert\NotBlank()
    */
  private $name;

  /**
   * @var text $value
   * @ORM\Column( type="text" )
   * @Assert\NotBlank()
   */
  private $value;

 

  

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
     * @return TabColumnTerms
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
     * Set value
     *
     * @param string $value
     * @return TabColumnTerms
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set tab_column
     *
     * @param \HootSuite\BackofficeBundle\Entity\TabColumn $tabColumn
     * @return TabColumnTerms
     */
    public function setTabColumn(\HootSuite\BackofficeBundle\Entity\TabColumn $tabColumn = null)
    {
        $this->tab_column = $tabColumn;

        return $this;
    }

    /**
     * Get tab_column
     *
     * @return \HootSuite\BackofficeBundle\Entity\TabColumn 
     */
    public function getTabColumn()
    {
        return $this->tab_column;
    }

    
}
