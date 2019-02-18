<?php
namespace Stagem\ZfcStatus\Form;

use Zend\Form\Form,
	Zend\InputFilter\Factory as InputFactory,
	Zend\InputFilter\InputFilter;

class Status extends Form {

	public function __construct($id, $dbAdapter)
	{
		parent::__construct('status');

		$this->setAttribute('method', 'post');


		$this->add([
			'name' => 'entityId',
			'attributes' => [
				'required' => 'required'
			],
		]);
		$this->add([
			'name' => 'name',
			'attributes' => [
				'required' => 'required'
			],
		]);


		// filters
		$inputFilter = new InputFilter();
		$factory = new InputFactory();


		$inputFilter->add($factory->createInput(array(
			'name'	=> 'entityId',
			'required' => true,
			'validators' => array(
				['name' => 'Digits']
			)
		)));

		$inputFilter->add($factory->createInput(array(
			'name'	=> 'name',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
			),
			'validators' => array(
				array(
					'name' => 'StringLength',
					'options' => array(
						'encoding' => 'UTF-8',
						'max' => 255
					)
				),
				/*array(
					'name' => 'Db\NoRecordExists',
					'options' => array(
						'table' => 'status',
						'field' => 'name',
						'adapter' => $dbAdapter,
						'exclude' => array(
							'field' => 'id',
							'value' => (int) $id,
						),
					)
				),*/
				array(
					'name' => '\Magere\Popov\Validator\Db\NoRecordExists',
					'options' => array(
						'table' => 'status',
						'field' => 'name',
						'fields' => ['entityId' => '?'],
						'adapter' => $dbAdapter,
						'exclude' => array(
							'field' => 'id',
							'value' => (int) $id,
						),
					)
				)
			)
		)));


		$this->setInputFilter($inputFilter);
	}

}