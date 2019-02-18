<?php
/**
 * Global status buttons panel
 *
 * @category Popov
 * @package Popov_ZfcStatus
 * @author Popov Sergiy <popow.serhii@gmail.com>
 * @datetime: 19.03.2016 23:36
 */
namespace Stagem\ZfcStatus\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

//use DoctrineModule\Persistence\ProvidesObjectManager;
//use DoctrineModule\Persistence\ObjectManagerAwareInterface;

class ButtonFieldset extends Fieldset /*implements InputFilterProviderInterface, ObjectManagerAwareInterface*/ {

	//use ProvidesObjectManager;

	public function init() {
		$this->setName('buttons');

		// buttons will be added through statusable plugin
	}

}