<?php
namespace Agere\Status\Model\Repository;

use Agere\Base\App\Exception;
use Doctrine\ORM\Query\ResultSetMapping;
use	Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\EntityRepository;
use Zend\Debug\Debug;

class StatusRepository extends EntityRepository {

	protected $_table = 'status';
	protected $_alias = 's';

	public function getStatuses($pool)
	{
		$module = 'module';
		$qb = $this->createQueryBuilder($this->_table)
			->leftJoin($this->_table . '.module', $module)
		;
		$qb->where($qb->expr()->in($this->_table . '.pool', '?1'));
		$qb->setParameter(1, $pool);
		return $qb;
	}
	
	public function getStatutesByModule($module)
	{
		$qb = $this->getStatuses();
		$qb->where($qb->expr()->in('module.namespace', '?1'));
		$qb->setParameter(1, $module);

		return $qb->getQuery()->getArrayResult();
	}

	public function getStatusAutomaticallyByModule($module)
	{
		$m = 'module';
		$qb = $this->createQueryBuilder($this->_table)
			->leftJoin($this->_table . '.module', $m)
		;
		$qb->where(
			$qb->expr()->andX(
				$qb->expr()->eq($m . '.namespace', '?1'),
				$qb->expr()->eq($this->_table . '.automatically', '?2')
			)
		);
		$qb->setParameters([1 => $module, 2 => 1]);
		//@todo Improve it 
		$status = $this->find($qb->getQuery()->getArrayResult()[0]['id']);

		return $status;
	}



	/**
	 * @param string|array $mnemo
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	/*public function findByMnemo($mnemo) {
		if (!is_array($mnemo)) {
			$mnemo = (array) $mnemo;
		}

		$qb = $this->createQueryBuilder($this->_alias);
		$qb->where($qb->expr()->andx(
			$qb->expr()->in('status.mnemo', '?1')
		))->setParameter(1, $mnemo); // hardcode for RAZ #2
		//$qb->orderBy('city.priority', 'DESC');

		return $qb;
	}*/

	/**
	 * @param string $entityMnemo
	 * @param null|string $hidden
	 * @return array
	 * @deprecated
	 */
	public function findAllStatuses($entityMnemo = '', $hidden = '')
	{
		$rsm = new ResultSetMapping();

		$rsm->addEntityResult($this->getEntityName(), $this->_alias);
		$rsm->addFieldResult($this->_alias, 'id', 'id');
		$rsm->addFieldResult($this->_alias, 'name', 'name');
		$rsm->addFieldResult($this->_alias, 'entityId', 'entityId');
		$rsm->addScalarResult('mnemo', 'mnemo');

		$data = [];
		$join = '';
		$where = '';

		if ($entityMnemo != '')
		{
			$join = 'AND e.`mnemo` = ?';
			$data[] = $entityMnemo;
		}

		if ($hidden != '')
		{
			$where = "WHERE {$this->_alias}.`hidden` = ?";
			$data[] = $hidden;
		}

		$sql = "SELECT {$this->_alias}.`id`, {$this->_alias}.`name`, {$this->_alias}.`entityId`, e.`mnemo`
			FROM `{$this->_table}` {$this->_alias}
			INNER JOIN `entity` e ON {$this->_alias}.`entityId` = e.`id` {$join}
			{$where}
			ORDER BY mnemo, name";
		$query = $this->_em->createNativeQuery($sql, $rsm);

		//\Zend\Debug\Debug::dump([$sql, $entityMnemo, $hidden]); die(__METHOD__);

		$query = $this->setParametersByArray($query, $data);

		return $query->getResult();
	}

	/**
	 * @param string|array $entityMnemo
	 * @param string $mnemo, possible keys: all, empty, notEmpty
	 * @return array
	 */
	public function findItems($entityMnemo = '', $mnemo = 'all')
	{
		$rsm = new ResultSetMappingBuilder($this->_em);
		$rsm->addRootEntityFromClassMetadata($this->getEntityName(), $this->_alias);

		$data = [];
		$join = '';
		$where = '';

		if ($entityMnemo)
		{
			if (is_string($entityMnemo))
			{
				$join = 'AND e.`mnemo` = ?';
				$data[] = $entityMnemo;
			}
			else if (is_array($entityMnemo))
			{
				$idsMnemoIn = $this->getIdsIn($entityMnemo);
				$join = "AND e.`mnemo` IN ({$idsMnemoIn})";
				$data = array_merge($data, $entityMnemo);
			}
		}

		switch ($mnemo)
		{
			case 'empty':
				$where .= "AND {$this->_alias}.`mnemo` = ''";
				break;
			case 'notEmpty':
				$where .= "AND {$this->_alias}.`mnemo` != ''";
				break;
		}

		$query = $this->_em->createNativeQuery(
			"SELECT {$this->_alias}.*
			FROM {$this->_table} {$this->_alias}
			INNER JOIN `entity` e ON {$this->_alias}.`entityId` = e.`id` {$join}
			WHERE 1 > 0 {$where}",
			$rsm
		);

		$query = $this->setParametersByArray($query, $data);

		return $query->getResult();
	}

	/**
	 * @param int $id
	 * @param string $field
	 * @return mixed
	 */
	public function findOneItem($id, $field = 'id')
	{
		$rsm = new ResultSetMappingBuilder($this->_em);
		$rsm->addRootEntityFromClassMetadata($this->getEntityName(), $this->_alias);

		$query = $this->_em->createNativeQuery(
			"SELECT {$this->_alias}.*
			FROM {$this->_table} {$this->_alias}
			WHERE {$this->_alias}.`$field` = ?
			LIMIT 1",
			$rsm
		);

		$query = $this->setParametersByArray($query, [$id]);

		$result = $query->getResult();

		if (count($result) == 0)
		{
			$result = $this->createOneItem();
		}
		else
		{
			$result = $result[0];
		}

		return $result;
	}

	/**
	 * @param string $name
	 * @param string $namespace
	 * @return mixed
	 */
	public function findOneItemByName($name, $namespace)
	{
		$rsm = new ResultSetMapping;

		$rsm->addEntityResult($this->getEntityName(), $this->_alias);
		$rsm->addFieldResult($this->_alias, 'id', 'id');
		$rsm->addFieldResult($this->_alias, 'name', 'name');
		$rsm->addFieldResult($this->_alias, 'mnemo', 'mnemo');
		$rsm->addFieldResult($this->_alias, 'entityId', 'entityId');
		$rsm->addFieldResult($this->_alias, 'hidden', 'hidden');

		$query = $this->_em->createNativeQuery(
			"SELECT {$this->_alias}.`id`, {$this->_alias}.`name`, {$this->_alias}.`mnemo`, {$this->_alias}.`entityId`,
			{$this->_alias}.`hidden`
			FROM {$this->_table} {$this->_alias}
			INNER JOIN `entity` e ON {$this->_alias}.`entityId` = e.`id` AND e.`namespace` = ?
			WHERE {$this->_alias}.`name` = ?
			LIMIT 1",
			$rsm
		);

		$query = $this->setParametersByArray($query, [$namespace, $name]);

		$result = $query->getResult();

		if (count($result) == 0)
		{
			$result = $this->createOneItem();
		}
		else
		{
			$result = $result[0];
		}

		return $result;
	}

	/**
	 * @param string $statusMnemo
	 * @param string $entityMnemo
	 * @return mixed
	 */
	public function findOneItemByMnemo($statusMnemo, $entityMnemo)
	{
		$rsm = new ResultSetMapping;

		$rsm->addEntityResult($this->getEntityName(), $this->_alias);
		$rsm->addFieldResult($this->_alias, 'id', 'id');
		$rsm->addFieldResult($this->_alias, 'name', 'name');
		$rsm->addFieldResult($this->_alias, 'mnemo', 'mnemo');

		$sql = "SELECT {$this->_alias}.`id`, {$this->_alias}.`name`, {$this->_alias}.`mnemo`
			FROM `{$this->_table}` {$this->_alias}
			INNER JOIN `entity` e ON {$this->_alias}.`entityId` = e.`id` AND e.`mnemo` = ?
			WHERE {$this->_alias}.`mnemo` = ?
			LIMIT 1";

		$query = $this->_em->createNativeQuery($sql, $rsm);

		//\Zend\Debug\Debug::dump([$sql, $entityMnemo, $statusMnemo]);// die(__METHOD__);

		$query = $this->setParametersByArray($query, [$entityMnemo, $statusMnemo]);

		$result = $query->getResult();

		if ($result)
		{
			$result = $result[0];
		}

		return $result;
	}

}