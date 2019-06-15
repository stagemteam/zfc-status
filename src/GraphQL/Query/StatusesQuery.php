<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2019 Bielov Andrii
 * This source file is subject to The MIT License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/MIT
 *
 * @category Stagem
 * @package Stagem_<package>
 * @author Bielov Andrii <bielovandrii@gmail.com>
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace Stagem\ZfcStatus\GraphQL\Query;

use GraphQL\Doctrine\Types;
use GraphQL\Type\Definition\Type;
use Stagem\ZfcStatus\Model\Status;

class StatusesQuery
{
    public function __invoke(Types $types)
    {
        return [
            'statuses' => [
                'type' => Type::listOf($types->getOutput(Status::class)),
                'args' => [
                    [
                        'name' => 'filter',
                        'type' => $types->getFilter(Status::class),
                    ],
                    [
                        'name' => 'sorting',
                        'type' => $types->getSorting(Status::class),
                    ],
                ],
                'resolve' => function ($root, $args) use ($types) {
                    $queryBuilder = $types->createFilteredQueryBuilder(Status::class, $args['filter'] ?? [],
                        $args['sorting'] ?? []);
                    $result = $queryBuilder->getQuery()->getResult();

                    return $result;
                },
            ],
        ];
    }
}