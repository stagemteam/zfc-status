<?php
/**
 * Status workflow role
 *
 * @category Popov
 * @package Popov_ZfcStatus
 * @author Popov Sergiy <popow.serhii@gmail.com>
 * @datetime: 17.03.2016 19:31
 */

namespace Popov\ZfcStatus\Model;

use Doctrine\ORM\Mapping as ORM;
use Popov\ZfcCore\Model\DomainAwareTrait;

/**
 * @ORM\Entity()
 * @ORM\Table(name="status_rule")
 */
class Rule
{
    use DomainAwareTrait;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(name="conditions", type="text", nullable=false)
     */
    protected $conditions;

    /**
     * The higher the number, the higher the priority
     *
     * @var integer
     * @ORM\Column(name="priority", type="integer", nullable=false)
     */
    protected $priority = 0;

    /**
     * @ORM\OneToOne(targetEntity="Status", inversedBy="rule")
     * @ORM\JoinColumn(name="statusId", referencedColumnName="id")
     */
    protected $status;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Rule
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Rule
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param mixed $conditions
     * @return Rule
     */
    public function setConditions($conditions)
    {
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param mixed $priority
     * @return Rule
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return Rule
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }
}