<?php
/**
 * @category Stagem
 * @package Stagem_Question
 * @author Kozak Vlad <vlad.gem.typ@gmail.com>
 * @datetime: 04.01.2018 16:14
 */

namespace Popov\ZfcStatus\Form;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineModule\Persistence\ProvidesObjectManager;
use Popov\ZfcEntity\Model\Entity;
use Stagem\Amazon\Model\Marketplace;
use Stagem\ZfcPool\Model\PoolInterface;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Form\Fieldset;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Stagem\ZfcTranslator\TranslatorAwareTrait;

class StatusFieldset extends Fieldset
    implements InputFilterProviderInterface,
    ObjectManagerAwareInterface
    //TranslatorAwareInterface
{
    use ProvidesObjectManager;
    //use TranslatorAwareTrait;

    public function init()
    {
        $this->setName('status');

        $this->add([
            'type' => 'hidden',
            'name' => 'id',
        ]);

        $this->add([
            'name' => 'name',
            'type' => 'text',
            /*'options' => [
                'label' => 'Question',
            ],*/
            'attributes' => [
                //'id' => 'description',
                //'class' => 'question form-control autocomplete',
                'placeholder' => 'name',
                'class' => 'question',
                //'placeholder' => $this->translate('Enter your question'),
                //'rows' => 5,
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'mnemo',
            'type' => 'text',
            /*'options' => [
                'label' => 'Question',
            ],*/
            'attributes' => [
                //'id' => 'description',
                //'class' => 'question form-control autocomplete',
                'placeholder' => 'mnemo',
                'class' => 'question',
                //'placeholder' => $this->translate('Enter your question'),
                //'rows' => 5,
                'required' => true,
            ],
        ]);
        /*$this->add([
            'name' => 'hidden',
            'type ' => 'checkbox',
            'attributes' => [
                'placeholder' => 'hidden',
                'required' => true

            ],
        ]);*/

        $this->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'hidden',
            'options' => [
                //'label' => $this->translate('Right'),
                'label' => 'Hidden',
                'use_hidden_element' => true,
                'checked_value' => 1,
                'unchecked_value' => 0,
            ],
            'attributes' => [
                'value' => '0',
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'automatically',
            'options' => [
                //'label' => $this->translate('Right'),
                'label' => 'Automatically',
                'use_hidden_element' => true,
                'checked_value' => 1,
                'unchecked_value' => 0,
            ],
            'attributes' => [
                'value' => '0',
                'class' => 'form-control',
            ],
        ]);

        /*$this->add([
            'name' => 'answers',
            'type' => 'Zend\Form\Element\Collection',
            'options' => [
                //'label' => $this->translate('Answer'),
                'count' => 1,
                'should_create_template' => true,
                'allow_add' => true,
                'allow_remove' => true,
                'target_element' => ['type' => \Stagem\Question\Form\AnswerFieldset::class],
            ],
        ]);*/


        $this->add([
            'name' => 'color',
            'type' => 'text',
            /*'options' => [
                'label' => 'Question',
            ],*/
            'attributes' => [
                //'id' => 'description',
                //'class' => 'question form-control autocomplete',
                'placeholder' => 'color',
                'class' => 'question',
                //'placeholder' => $this->translate('Enter your question'),
                //'rows' => 5,
                'required' => true,
            ],
        ]);

        $this->add([
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'pool',
            'options' => [
                'object_manager' => $this->getObjectManager(),
                'target_class' => PoolInterface::class,
                'property' => 'domain',
                'label'    => 'Choose Domain',
                //'is_method' => true,
                /*'find_method' => [
                    'name' => 'findServiceByServiceCategoryMnemo',
                    'params' => [
                        'criteria' => ['orthopedic'],
                    ],
                ],*/
            ],
        ]);

        $this->add([
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'entity',
            'options' => [
                'object_manager' => $this->getObjectManager(),
                'target_class' => Entity::class,
                'property' => 'mnemo',
                'label'    => 'Choose Entity',
                //'is_method' => true,
                /*'find_method' => [
                    'name' => 'findServiceByServiceCategoryMnemo',
                    'params' => [
                        'criteria' => ['orthopedic'],
                    ],
                ],*/
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
        return [];
    }
}
