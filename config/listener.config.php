<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2018 Serhii Popov
 * This source file is subject to The MIT License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/MIT
 *
 * @category Popov
 * @package Popov_ZfcStatus
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 */

use Stagem\ZfcStatus\Controller\StatusController;
use Stagem\ZfcStatus\Listener\StatusListener;

return [
    'definitions' => [
        [
            'listener' => StatusListener::class,
            'method' => 'postChange',
            'event' => 'change.post',
            'identifier' => StatusController::class,
            'priority' => 110,
        ],
    ],
];
