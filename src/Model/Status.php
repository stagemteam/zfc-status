<?php

namespace Stagem\ZfcStatus\Model;

use Doctrine\ORM\Mapping as ORM;
use Popov\ZfcCore\Model\DomainAwareTrait;
use Popov\ZfcEntity\Model\Entity;
use Stagem\ZfcPool\Model\PoolInterface;

/**
 * @ORM\Entity(repositoryClass="Stagem\ZfcStatus\Model\Repository\StatusRepository")
 * @ORM\Table(name="status")
 */
class Status
{
    use DomainAwareTrait;

    const MNEMO = 'status';

    const TABLE = 'status';

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="mnemo", type="string", length=32, nullable=false)
     */
    private $mnemo;

    /**
     * @var integer
     */
    #private $entityId;

    /**
     * @var integer
     * @ORM\Column(name="hidden", type="smallint", length=1, nullable=false)
     */
    private $hidden = 0;

    /**
     * @var integer
     * @ORM\Column(name="automatically", type="smallint", length=1, nullable=true)
     */
    private $automatically = 0;

    /**
     * HEX color code, for example #ffffff
     *
     * @var string
     * @ORM\Column(name="color", type="string", length=7, nullable=true)
     */
    private $color;

    /**
     * @var Entity
     * @ORM\ManyToOne(targetEntity="Popov\ZfcEntity\Model\Entity", cascade={"persist", "remove"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entityId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $entity;

    /**
     * @var PoolInterface
     * @ORM\ManyToOne(targetEntity="Stagem\ZfcPool\Model\PoolInterface", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="poolId", referencedColumnName="id", nullable=true)
     * })
     */
    private $pool;

    /**
     * @var \Doctrine\Common\Collections\Collection|Status[]
     * @ORM\ManyToMany(targetEntity="Status", inversedBy="workflow")
     * @ORM\JoinTable(
     *  name="status_workflow",
     *  joinColumns={
     *      @ORM\JoinColumn(name="statusId", referencedColumnName="id")
     *  },
     *  inverseJoinColumns={
     *      @ORM\JoinColumn(name="nextStatusId", referencedColumnName="id")
     *  })
     */
    private $workflow;

    /**
     * @var Rule
     * @ORM\OneToOne(targetEntity="Rule", mappedBy="status")
     */
    private $rule;

    public function __construct()
    {
        $this->workflow = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Status
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
     * Set mnemo
     *
     * @param string $mnemo
     * @return Status
     */
    public function setMnemo($mnemo)
    {
        $this->mnemo = $mnemo;

        return $this;
    }

    /**
     * Get mnemo
     *
     * @return string
     */
    public function getMnemo()
    {
        return $this->mnemo;
    }

    /**
     * Set hidden
     *
     * @param string $hidden
     * @return Status
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * Get hidden
     *
     * @return string
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Set automatically
     *
     * @param string $automatically
     * @return Status
     */
    public function setAutomatically($automatically)
    {
        $this->automatically = $automatically;

        return $this;
    }

    /**
     * Get automatically
     *
     * @return string
     */
    public function getAutomatically()
    {
        return $this->automatically;
    }

    /**
     * Set color
     *
     * @param string $color
     * @return Status
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set entity
     *
     * @param Entity $entity
     * @return Status
     */
    public function setEntity(Entity $entity = null)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get entity
     *
     * @return Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }


    /**
     * @return Pool
     */
    public function getPool(): Pool
    {
        return $this->pool;
    }

    /**
     * @param Pool $pool
     * @return Status
     */
    public function setPool(Pool $pool): Status
    {
        $this->pool = $pool;

        return $this;
    }

    /**
     * @return Status[]
     */
    public function getWorkflow()
    {
        return $this->workflow;
    }

    /**
     * @param Status[] $workflow
     * @return Status
     */
    public function setWorkflow($workflow)
    {
        $this->workflow = $workflow;

        return $this;
    }

    /**
     * @return Rule
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * @param Rule $rule
     * @return Status
     */
    public function setRule($rule)
    {
        $this->rule = $rule;

        return $this;
    }
}
