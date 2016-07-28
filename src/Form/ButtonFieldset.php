<?php
/**
 * Global status buttons panel
 *
 * @category Agere
 * @package Agere_Status
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 19.03.2016 23:36
 */
namespace Agere\Status\Form;

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