<?php

namespace HootSuite\BackofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
/**
 * @ORM\Table(name="organization")
 * @ORM\Entity()
 */
class Organization
{
  /**
   * @var integer $id
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

   /**
    * @ORM\OneToMany(targetEntity="Member", mappedBy="organization", cascade={"persist","remove"})
    */
  private $members;

    /**
     * @ORM\OneToMany(targetEntity="Team", mappedBy="organization", cascade={"persist","remove"})
     */
    private $teams;
  
  /**
   * @var string $name
   * @ORM\Column( type="string", length=50)
   * @Assert\NotBlank()
   */
  private $name;

    /**
     * @var datetime $createdAt
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var datetime $updatedAt
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;
    /**
     * @var User $createdBy
     *
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Usuario")
     * @ORM\JoinColumn(name="createdBy", referencedColumnName="id")
     */
    private $createdBy;

    /**
     * @var User $updatedBy
     *
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="Usuario")
     * @ORM\JoinColumn(name="updatedBy", referencedColumnName="id")
     */
    private $updatedBy;
  
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->teams = new ArrayCollection();
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
     * @return Organization
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
     * Add members
     *
     * @param \HootSuite\BackofficeBundle\Entity\Member $members
     * @return Organization
     */
    public function addMember(Member $members)
    {
        $this->members[] = $members;

        return $this;
    }

    /**
     * Remove members
     *
     * @param \HootSuite\BackofficeBundle\Entity\Member $members
     */
    public function removeMember(Member $members)
    {
        $this->members->removeElement($members);
    }

    /**
     * Get members
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMembers()
    {
        return $this->members;
    }
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    public function setCreatedBy(Usuario $user)
    {
        $this->createdBy = $user;
        $this->setUpdatedBy($user);
    }

    public function setUpdatedBy(Usuario $user)
    {
        $this->updatedBy = $user;
    }
    public function setCreatedAt($date)
    {
        $this->createdAt = $date;
        $this->setUpdatedAt($date);
    }

    public function setUpdatedAt($date)
    {
        $this->updatedAt = $date;
    }

    /**
     * Add teams
     *
     * @param \HootSuite\BackofficeBundle\Entity\Team $teams
     * @return Organization
     */
    public function addTeam(Team $team)
    {
        $this->teams[] = $team;
        return $this;
    }

    /**
     * Remove teams
     *
     * @param \HootSuite\BackofficeBundle\Entity\Team $teams
     */
    public function removeTeam(Team $teams)
    {
        $this->teams->removeElement($teams);
    }

    /**
     * Get teams
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTeams()
    {
        return $this->teams;
    }
}
