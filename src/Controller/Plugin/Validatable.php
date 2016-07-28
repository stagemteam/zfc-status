<?php
/**
 * Plugin which apply validation group relative to status.
 *
 * @category Agere
 * @package Agere_Status
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 11.05.16 2:21
 */

namespace Agere\Status\Controller\Plugin;

use Zend\Stdlib\Exception;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\Controller\Plugin\Url;
use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\Form\Element\Collection as FormCollection;

use Agere\Entity\Service\EntityService as ModuleService;
use Agere\Status\Service\StatusChanger;
use Agere\Status\Form\ButtonFieldset;

use Agere\Current\Plugin\Current;

/**
 * Class Validatable
 *
 * @method \Magere\Status\Controller\Plugin\Statusable statusable()
 * @method \Agere\Current\Plugin\Current current()
 */
class Validatable extends AbstractPlugin {

	/** @var [] */
	protected $config;

	public function injectConfig($config) {
		$this->config = $config;

		return $this;
	}

	public function getConfig() {
		return $this->config;
	}

	/**
	 * Apply validation group to from
	 *
	 * @param Form $form Bind method of Form must be called before
	 */
	public function apply(Form $form)
    {
        $config = $this->getConfig();
        $current = $this->getController()->current();
        $statusable = $this->getController()->statusable();
        $status = $statusable->getStatus($form->getObject());
        $namespace = $current->currentModule($form->getObject());

        //\Zend\Debug\Debug::dump($namespace); die(__METHOD__);
        //\Zend\Debug\Debug::dump($status->getMnemo()); die(__METHOD__);
        if (isset($config['status']['validation'][$namespace][$status->getMnemo()])) {
            $statusFields = $config['status']['validation'][$namespace][$status->getMnemo()];
            //\Zend\Debug\Debug::dump($statusFields); die(__METHOD__);
            if ($statusFields) {
                $validationGroup = [];
                $this->applyOptions($statusFields, $form, $validationGroup);
                if ($validationGroup) {
                    $form->setValidationGroup($validationGroup);
                }
            }
        }

	}

	protected function applyOptions($validationOptions, $formOrElement, & $validationGroup = [])
    {
		//\Zend\Debug\Debug::dump($validationGroup);
		//\Zend\Debug\Debug::dump($validationOptions);

		foreach ($validationOptions as $group => $validation) {
			$targetElement = ($formOrElement instanceof FormCollection)
				? $formOrElement->getTargetElement()
				: $formOrElement;

			if (substr($group, 0, 2) === '__') { // prepare optional fields
				$optionalMethod = 'prepareOptional' . ucfirst(substr($group, 2));
				if (method_exists($this, $optionalMethod)) {
					$this->{$optionalMethod}($validation, $formOrElement);
				}
			} elseif (($targetElement instanceof Fieldset && $targetElement->has($group))
				&& (($targetElement = $targetElement->get($group)) instanceof Fieldset)
			) {
				$validationGroup[$group] = [];
				$this->applyOptions($validation, $targetElement, $validationGroup[$group]);
			} else {
				if (is_array($validation)) { // is attributes attached to element in config
					$validationGroup[] = $group;
					foreach ($validation as $attr => $value) {
						$targetElement->setAttribute($attr, $value);

						//\Zend\Debug\Debug::dump($attr, $value);
						//\Zend\Debug\Debug::dump($formOrElement->getAttribute($attr));
					}
				} elseif (is_string($validation)) {
					$validationGroup[] = $validation;
				} else {
					throw new Exception\RuntimeException(sprintf(
						'Unsupported type "%s" placed in validation groups.', gettype($validation)
					));
				}
			}
		}
	}

	protected function prepareOptionalConfig($config, $formOrElement)
    {
		if (isset($config['hydratorStrategy'])) {
            // @link https://github.com/doctrine/DoctrineModule/blob/master/docs/hydrator.md
			$class = 'DoctrineModule\\Stdlib\\Hydrator\\Strategy\\' . $config['hydratorStrategy'];

            //$hydrator = $formOrElement->getHydrator();
            ##$hydrator->addStrategy($formOrElement->getName(), new $class());
            //\Zend\Debug\Debug::dump([$formOrElement->getName(), get_class($hydrator->getStrategy($formOrElement->getName()))]); die(__METHOD__);

            $targetElement = $formOrElement->getTargetElement();
            /** @var \DoctrineModule\Stdlib\Hydrator\DoctrineObject $hydrator */
            $hydrator = $targetElement->getHydrator();
			$hydrator->addStrategy($targetElement->getName(), new $class());

			//$targetElement = $this->form->get('invoice')->get('invoiceProducts')->getTargetElement();
			/** @var \DoctrineModule\Stdlib\Hydrator\DoctrineObject $hydrator */
			//$hydrator = $targetElement->getHydrator();
            /*\Zend\Debug\Debug::dump([
                $targetElement->getName(),
                get_class($formOrElement),
                //get_class($hydrator->getStrategy('quantityItems')),
                get_class($hydrator),
                get_class($targetElement->getHydrator()),
                get_class($targetElement),
            ]);
			die(__METHOD__);*/


			//'hydrator' => ['addStrategy' => 'DisallowRemoveByValue']
			//\Zend\Debug\Debug::dump([$formOrElement->getName(), get_class($targetElement->getHydrator()), $config]); die(__METHOD__);
		}
	}

	public function __invoke()
    {
		if (!$args = func_get_args()) {
			return $this;
		}

		return call_user_func([$this, 'apply'], func_get_args());
	}
}
