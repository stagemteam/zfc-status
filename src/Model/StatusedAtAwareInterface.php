<?php

namespace Stagem\ZfcStatus\Model;

use DateTime;

interface StatusedAtAwareInterface
{
    /**
     * @param DateTime $dateTime
     * @return void
     */
    public function setStatusedAt(DateTime $dateTime);

    /**
     * @return DateTime
     */
    public function getStatusedAt();
}