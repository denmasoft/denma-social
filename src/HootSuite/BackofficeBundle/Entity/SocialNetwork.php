<?php

namespace HootSuite\BackofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Table(name="social_network")
 * @ORM\Entity(repositoryClass="SocialNetworkRepository")
 */
class SocialNetwork
{
  /**
   * @var integer $id
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;
  
   /**
    * @ORM\OneToMany(targetEntity="ProfilesUsuario", mappedBy="social_network", cascade={"persist","remove"})
    */
  private $profiles;
  
   /**
    * @ORM\OneToMany(targetEntity="Column", mappedBy="social_network")
    */
  private $columns;
  
   /**
    * @ORM\OneToMany(targetEntity="SocialList", mappedBy="social_network", cascade={"persist","remove"})
    */
  private $lists;
  
   /**
    * @ORM\OneToMany(targetEntity="Report", mappedBy="social_network", cascade={"persist","remove"})
    */
  private $reports;
 
  /** 
    * @var string $name
    * @ORM\Column(type="string", length=100)
    */
  protected $name;
 
  /** 
    * @var string $uniquename
    * @ORM\Column(type="string", length=100)
    */
  protected $uniquename;
 
  /** 
    * @var string $avatar
    * @ORM\Column(type="string", length=255)
    */
  protected $avatar;
 
  /** 
    * @var boolean $privacity
    * @ORM\Column(type="boolean")
    */
  protected $privacity;
 
  /** 
    * @var boolean $segmentation
    * @ORM\Column(type="boolean")
    */
  protected $segmentation;
 
  /** 
    * @var string $glyphicon
    * @ORM\Column(type="string", length=50)
    */
  protected $glyphicon;
 
  /** 
    * @var string $glyphicon1
    * @ORM\Column(type="string", length=50)
    */
  protected $glyphicon1;
 
  /** 
    * @var boolean $keyword
    * @ORM\Column(type="boolean")
    */
  protected $keyword;
 
  /** 
    * @var boolean $search
    * @ORM\Column(type="boolean")
    */
  protected $search;
  
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->profiles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->lists = new \Doctrine\Common\Collections\ArrayCollection();
        $this->reports = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return SocialNetwork
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
     * Set uniquename
     *
     * @param string $uniquename
     * @return SocialNetwork
     */
    public function setUniquename($uniquename)
    {
        $this->uniquename = $uniquename;

        return $this;
    }

    /**
     * Get uniquename
     *
     * @return string 
     */
    public function getUniquename()
    {
        return $this->uniquename;
    }

    /**
     * Set avatar
     *
     * @param string $avatar
     * @return SocialNetwork
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
     * Set privacity
     *
     * @param boolean $privacity
     * @return SocialNetwork
     */
    public function setPrivacity($privacity)
    {
        $this->privacity = $privacity;

        return $this;
    }

    /**
     * Get privacity
     *
     * @return boolean 
     */
    public function getPrivacity()
    {
        return $this->privacity;
    }

    /**
     * Set segmentation
     *
     * @param boolean $segmentation
     * @return SocialNetwork
     */
    public function setSegmentation($segmentation)
    {
        $this->segmentation = $segmentation;

        return $this;
    }

    /**
     * Get segmentation
     *
     * @return boolean 
     */
    public function getSegmentation()
    {
        return $this->segmentation;
    }

    /**
     * Set glyphicon
     *
     * @param string $glyphicon
     * @return SocialNetwork
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
     * Set glyphicon1
     *
     * @param string $glyphicon1
     * @return SocialNetwork
     */
    public function setGlyphicon1($glyphicon1)
    {
        $this->glyphicon1 = $glyphicon1;

        return $this;
    }

    /**
     * Get glyphicon1
     *
     * @return string 
     */
    public function getGlyphicon1()
    {
        return $this->glyphicon1;
    }

    /**
     * Set keyword
     *
     * @param boolean $keyword
     * @return SocialNetwork
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;

        return $this;
    }

    /**
     * Get keyword
     *
     * @return boolean 
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * Set search
     *
     * @param boolean $search
     * @return SocialNetwork
     */
    public function setSearch($search)
    {
        $this->search = $search;

        return $this;
    }

    /**
     * Get search
     *
     * @return boolean 
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * Add profiles
     *
     * @param \HootSuite\BackofficeBundle\Entity\ProfilesUsuario $profiles
     * @return SocialNetwork
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
     * Add lists
     *
     * @param \HootSuite\BackofficeBundle\Entity\SocialList $lists
     * @return SocialNetwork
     */
    public function addList(\HootSuite\BackofficeBundle\Entity\SocialList $lists)
    {
        $this->lists[] = $lists;

        return $this;
    }

    /**
     * Remove lists
     *
     * @param \HootSuite\BackofficeBundle\Entity\SocialList $lists
     */
    public function removeList(\HootSuite\BackofficeBundle\Entity\SocialList $lists)
    {
        $this->lists->removeElement($lists);
    }

    /**
     * Get lists
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLists()
    {
        return $this->lists;
    }

    /**
     * Add reports
     *
     * @param \HootSuite\BackofficeBundle\Entity\Report $reports
     * @return SocialNetwork
     */
    public function addReport(\HootSuite\BackofficeBundle\Entity\Report $reports)
    {
        $this->reports[] = $reports;

        return $this;
    }

    /**
     * Remove reports
     *
     * @param \HootSuite\BackofficeBundle\Entity\Report $reports
     */
    public function removeReport(\HootSuite\BackofficeBundle\Entity\Report $reports)
    {
        $this->reports->removeElement($reports);
    }

    /**
     * Get reports
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * Add columns
     *
     * @param \HootSuite\BackofficeBundle\Entity\Column $columns
     * @return SocialNetwork
     */
    public function addColumn(\HootSuite\BackofficeBundle\Entity\Column $columns)
    {
        $this->columns[] = $columns;

        return $this;
    }

    /**
     * Remove columns
     *
     * @param \HootSuite\BackofficeBundle\Entity\Column $columns
     */
    public function removeColumn(\HootSuite\BackofficeBundle\Entity\Column $columns)
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
}
