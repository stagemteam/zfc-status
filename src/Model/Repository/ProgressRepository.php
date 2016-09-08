<?php
namespace Agere\Status\Model\Repository;

use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
/*use Magere\Agere\ORM\EntityRepository;*/
use Doctrine\ORM\EntityRepository;


class ProgressRepository extends EntityRepository {

    protected $_alias = 'statusProgress';

    public function getProgressItem($item, $module) {
        $u = 'user';
        $s = 'status';

        $qb = $this->createQueryBuilder($this->_alias)
            ->leftJoin($this->_alias . '.status', $s)
            ->leftJoin($this->_alias . '.user', $u);

        $qb->where(
			$qb->expr()->andX(
                $qb->expr()->eq($this->_alias . '.module', '?1'),
                $qb->expr()->eq($this->_alias . '.itemId', '?2')
			)
		);

        $qb->setParameters([1 => $module->getId(), 2 => $item->getId()]);

        //$query = $qb->getQuery();
        //\Zend\Debug\Debug::dump([$query->getSql(), $query->getParameters()]); die(__METHOD__);

        return $qb;
    }

    public function getProgressByPatient($patientId) {
        $u = 'user';
        $s = 'status';
        $m = 'module';
        $p = 'patient';

        $qb = $this->createQueryBuilder($this->_alias)
            ->leftJoin($this->_alias . '.user', $u)
            ->leftJoin($this->_alias . '.module', $m)
            ->leftJoin($this->_alias . '.status', $s)
            ->leftJoin($this->_alias . '.patient', $p)
            ->orderBy($this->_alias. '.modifiedAt', 'DESC')
        ;
        $qb->where(
            $qb->expr()->andX(
                $qb->expr()->eq($this->_alias . '.patient', '?1')
            )
        );

        $qb->setParameters([1 => $patientId]);
        return $qb;
    }

}