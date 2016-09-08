<?php
/**
 *
 * @category Agere
 * @package Agere_Status
 * @author Vlad Kozak <vk@agere.com.ua>
 * @datetime: 29.03.2016 23:14
 */
namespace Agere\Status\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Agere\Core\Model\DomainAwareTrait;
use Agere\Module\Model\Module;
use Agere\User\Model\User;

/**
 * @ORM\Entity(repositoryClass="Agere\Status\Model\Repository\StatusProgressRepository")
 * @ORM\Table(name="status_progress")
 */
class Progress {

    use DomainAwareTrait;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modifiedAt", type="datetime")
     */
    protected $modifiedAt;

    /** @ORM\Column(name="snippet", type="string", unique=false, nullable=false, length=255) */
    protected $snippet;


    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Agere\User\Model\User")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Agere\Status\Model\Status")
     * @ORM\JoinColumn(name="statusId", referencedColumnName="id")
     */
    protected $status;

    /**
     * @var integer
     * @ORM\Column(name="itemId", type="integer", nullable=false)
     */
    protected $itemId;

    /**
     * @var Module
     *
     * @ORM\ManyToOne(targetEntity="Agere\Module\Model\Module")
     * @ORM\JoinColumn(name="moduleId", referencedColumnName="id")
     */
    protected $module;

    /**
     * @var Module
     *
     * @ORM\ManyToOne(targetEntity="Agere\Patient\Model\Patient")
     * @ORM\JoinColumn(name="patientId", referencedColumnName="id")
     */
    protected $patient;

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $itemId
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;

        return $this;
    }

    /**
     * @return int
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @param \DateTime $modifiedAt
     * @return $this
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    /**
     * @return \Magere\Status\Model\datetime
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * @param \Magere\Entity\Model\Entity $module
     * @return $this
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * @return \Magere\Entity\Model\Entity
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param mixed $snippet
     */
    public function setSnippet($snippet)
    {
        $this->snippet = $snippet;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSnippet()
    {
        return $this->snippet;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;

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
     * @param \Agere\User\Model\User $user
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \Agere\User\Model\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Module
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * @param Module $patient
     * @return Progress
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;

        return $this;
    }

}
