<?php
namespace Agere\Status\Model;

use Doctrine\ORM\Mapping as ORM;
use Popov\ZfcCore\Model\DomainAwareTrait;

/**
 * Status
 */
class Status {

    use DomainAwareTrait;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $mnemo;

    /**
     * @var integer
     */
    private $moduleId;

    /**
     * @var string
     */
    private $hidden;

    /**
     * @var string
     */
    private $remove;

    /**
     * @var string
     */
    private $automatically;

    /**
     * @var string
     */
    private $color;

    /**
     * @var \Agere\Module\Model\Module
     */
    private $module;

    /** @var Status[] */
    private $workflow;

    /** @var Rule */
    private $rule;

    /** @var Progress */
    private $progress;

    /** @var Status[] */
    private $pool;

    /**
     * Constructor
     */
    public function __construct() {
        $this->workflow = new \Doctrine\Common\Collections\ArrayCollection();
        $this->progress = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * @param string $automatically
     */
    public function setAutomatically($automatically)
    {
        $this->automatically = $automatically;
    }

    /**
     * @return string
     */
    public function getAutomatically()
    {
        return $this->automatically;
    }

    /**
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param \Agere\Module\Model\Module $module
     */
    public function setModule($module)
    {
        $this->module = $module;
    }

    /**
     * @return \Agere\Module\Model\Module
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param int $moduleId
     */
    public function setModuleId($moduleId)
    {
        $this->moduleId = $moduleId;
    }

    /**
     * @return int
     */
    public function getModuleId()
    {
        return $this->moduleId;
    }

    /**
     * @param string $hidden
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * @return string
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $mnemo
     */
    public function setMnemo($mnemo)
    {
        $this->mnemo = $mnemo;
    }

    /**
     * @return string
     */
    public function getMnemo()
    {
        return $this->mnemo;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $remove
     */
    public function setRemove($remove)
    {
        $this->remove = $remove;
    }

    /**
     * @return string
     */
    public function getRemove()
    {
        return $this->remove;
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

    /**
     * @return Progress
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * @param Progress $progress
     * @return Status
     */
    public function setProgress($progress)
    {
        $this->progress = $progress;

        return $this;
    }

    /**
     * @return Status[]
     */
    public function getPool()
    {
        return $this->pool;
    }

    /**
     * @param Status[] $pool
     * @return Status
     */
    public function setPool($pool)
    {
        $this->pool = $pool;

        return $this;
    }

}
