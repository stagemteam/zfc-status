<?php
namespace Agere\Status\Form;

use Zend\Form\Form;

class StatusForm extends Form
{
    protected $objectManager;

    public function init()
    {
        $this->setName('status');

        $this->add([
            'name' => 'status',
            'type' => 'Agere\Status\Form\StatusFieldset',
            'options' => [
                'use_as_base_fieldset' => true,
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'attributes' => [
                'type' => 'submit',
                'value' => 'Send',
                'class' => 'btn btn-primary',
            ],
        ]);
    }
}