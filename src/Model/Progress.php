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
use Agere\Entity\Model\Entity as Module;
use User\Model\Entity\User as Users;
//use User\Model\Entity\User as Users;

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
     * @ORM\ManyToOne(targetEntity="Agere\Entity\Model\Entity")
     * @ORM\JoinColumn(name="moduleId", referencedColumnName="id")
     */
    protected $module;

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;
    }

    /**
     * @return \DateTime
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * @param \Agere\Entity\Model\Entity $module
     */
    public function setModule($module)
    {
        $this->module = $module;
    }

    /**
     * @return \Agere\Entity\Model\Entity
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
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param \Agere\Status\Model\User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return \Agere\Status\Model\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
