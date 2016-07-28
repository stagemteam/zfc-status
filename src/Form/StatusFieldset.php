<?php
namespace Agere\Status\Form;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineModule\Persistence\ProvidesObjectManager;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Form\Fieldset;

class StatusFieldset extends Fieldset implements InputFilterProviderInterface, ObjectManagerAwareInterface
{
    use ProvidesObjectManager;

    public function init()
    {
        $this->setName('status');

        $this->add([
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'id'
        ]);

        $this->add([
            'name' => 'name',
            'options' => [
                'label' => 'Название',
            ],
            'attributes' => [
                'id' => 'name',
                'class' => 'form-control',
                'placeholder' => 'Enter contract number...',
            ],
        ]);

        $this->add([
            'name' => 'mnemo',
            'options' => [
                'label' => 'mnemo',
            ],
            'attributes' => [
                'id' => 'mnemo',
                'class' => 'form-control',
                'placeholder' => 'Enter mnemo...',
            ],
        ]);

        $this->add([
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'module',
            'options' => [
                'object_manager' => $this->getObjectManager(),
              'target_class' => 'Agere\Module\Model\Module',
                'property' => 'mnemo',
            ],
        ]);
    }

    /**
     * Should return an array specification compatible with
     * {@link Zend\InputFilter\Factory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return [
            'name' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
            ],
        ];
    }
}
