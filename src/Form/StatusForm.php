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
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Stagem\ZfcTranslator\TranslatorAwareTrait;

class StatusForm extends Form //implements TranslatorAwareInterface
{
    use ProvidesObjectManager;
    //use TranslatorAwareTrait;

    public function init()
    {
        $this->setName('status');

        $this->add([
            'name' => 'status',
            'type' => StatusFieldset::class,
            'options' => [
                'use_as_base_fieldset' => true,
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'attributes' => [
                'type' => 'submit',
                'value' => 'Save question',
                'class' => 'btn btn-primary',
            ]
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
