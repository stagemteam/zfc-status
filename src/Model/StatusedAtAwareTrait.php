<?php

namespace Popov\ZfcStatus\Model;

use DateTime;

trait StatusedAtAwareTrait
{
    /**
     * @var DateTime
     *
     * @ORM\Column(name="statusedAt", type="datetime", nullable=false)
     */
    private $statusedAt;

    /**
     * @return DateTime
     */
    public function getStatusedAt()
    {
        return $this->statusedAt;
    }

    /**
     * @param DateTime $statusedAt
     */
    public function setStatusedAt(DateTime $statusedAt)
    {
        $this->statusedAt = $statusedAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        // Якщо це нова модель, то це значення не буде встановлено, а передавати null заборонено
        if (!$this->getStatusedAt()) {
            $this->setStatusedAt(new DateTime('now'));
        }
    }
}