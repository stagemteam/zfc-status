<?php
/**
 * @category Popov
 * @package Popov_Cart
 * @author Sergiy Popov <popow.serhii@gmail.com>
 * @datetime: 19.12.15 17:44
 */
namespace MagereTest\Cart\Service;

use Popov\Current\Plugin\Current;
use Stagem\ZfcStatus\Controller\Plugin\Statusable;
use Stagem\ZfcStatus\Controller\Plugin\Validatable;
use Stagem\ZfcStatus\Model\Status;
use Stagem\ZfcStatus\Service\StatusChanger;
use Mockery;
use Zend\Stdlib\Exception;
use Zend\Stdlib\ArrayUtils;
use Zend\ServiceManager\ServiceManager;
use PHPUnit_Framework_TestCase as TestCase;

class ValidatableTest extends TestCase
{
    /** @var Mockery */
    protected $itemMock;

    /** @var CartItem */
    protected $cartItem;

    /** @var CartItemService */
    protected $service;

    public function setUp()
    {
        $status = new Status();
        $status->setMnemo('acceptance');

        $this->itemMock = Mockery::mock('alias:Popov\Invoice\Model\Invoice');
        $this->itemMock->shouldReceive('getStatus')
            ->andReturn($status);
    }

    protected function tearDown()
    {
        \Mockery::close();
    }

    public function testGetStatusFormObjectAndApplyValidationBased()
    {
        $formMock = $this->getFormMock();
        $currentMock = $this->getCurrentMock($formMock->getObject());
        $statusableMock = $this->getStatusableMock($formMock->getObject());

        $validatable = $this->getValidatable();
        $validatable->setCurrent($currentMock);
        $validatable->setStatusable($statusableMock);

        $formMock->shouldReceive('setValidationGroup')->with(Mockery::on(function($arg) {
            return $arg === ['invoice' => ['invoiceProducts' => ['id']]];
        }));

        $validatable->apply($formMock);
    }

    public function testDefaultApplyViewModeValidation()
    {
        $formMock = Mockery::mock('Zend\Form\FieldsetInterface, Zend\Form\FormInterface');

        $validatableMock = Mockery::mock(Validatable::class . '[applyStatus]', [[]]);
        $validatableMock->shouldReceive('applyStatus')->andReturnNull();
        $validatableMock->apply($formMock);

        $this->assertTrue($validatableMock->getMode() === Validatable::MODE_VIEW);
    }

    public function testApplyViewModeValidationIfStatusNotPass()
    {
        $formMock = $this->getFormMock();
        $statusableMock = $this->getStatusableMock($formMock->getObject());

        $validatableMock = Mockery::mock(Validatable::class . '[applyStatus]', [[]]);
        $validatableMock->setStatusable($statusableMock);
        $validatableMock->shouldReceive('applyStatus')->andReturnNull();
        $validatableMock->apply($formMock);

        $this->assertTrue($validatableMock->getMode() === Validatable::MODE_VIEW);
    }


    public function testAutomaticallyApplyChangeModeValidation()
    {
        $itemMock = $this->getItemMock();
        $formMock = Mockery::mock('Zend\Form\FieldsetInterface, Zend\Form\FormInterface');

        $validatableMock = Mockery::mock(Validatable::class . '[applyStatus]', [[]]);
        $validatableMock->shouldReceive('applyStatus')->andReturnNull();
        $validatableMock->apply($formMock, $itemMock->getStatus());

        $this->assertTrue($validatableMock->getMode() === Validatable::MODE_CHANGE);
    }

    public function testSetDisabledAttributeConfig()
    {
        #$inputFilterMock = $this->getInputFilterMock();
        $fieldsetMock = $this->getFieldsetMock();

        $formMock = $this->getFormMock();
        $currentMock = $this->getCurrentMock($formMock->getObject());
        $statusableMock = $this->getStatusableMock($formMock->getObject());

        $validatable = $this->getValidatable();
        $validatable->setCurrent($currentMock);
        $validatable->setStatusable($statusableMock);

        $config = $validatable->getConfig();
        $config['status']['validation']['Popov\\Invoice']['view']['acceptance']['invoice']['invoiceProducts'] = [
            'id',
            'quantity' => [
                'disabled' => false,
            ],
        ];
        $validatable->setConfig($config);

        $elementMock = Mockery::mock('Zend\Form\ElementInterface');
        $elementMock->shouldReceive('setAttribute')->withArgs(function($attr, $value) {
            $this->assertEquals($attr, 'disabled');
            $this->assertFalse($value);

            return true;
        });
        $fieldsetMock->shouldReceive('get')->with('quantity')->andReturn($elementMock);

        // must be declared
        $formMock->shouldReceive('setValidationGroup')->with(Mockery::any());

        $validatable->apply($formMock);
    }

    private function getValidatable()
    {
        $fieldsetConfig = [
            'invoice' => [
                'invoiceProducts' => [
                    'id',
                    /*'quantityItems' => [
                        'id',
                        'quantity' => [
                            'disabled' => false,
                        ],
                        '__config' => [
                            'hydratorStrategy' => 'DisallowRemoveByValue',
                        ],
                        //'quantity' => true,
                        //'shelf' => false,
                    ],*/
                ],
            ],
        ];


        $config['status']['validation']['Popov\\Invoice']['view'] = [
            'new' => [],
            'acceptance' => $fieldsetConfig,
            'capitalized' => [],
        ];

        $validatable = new Validatable($config);

        return $validatable;
    }

    protected function getStatusableMock($item)
    {
        //$statusable = new Statusable(new StatusChanger());

        $statusableMock = Mockery::mock('alias:Magere\\Status\\Controller\\Plugin\\Statusable');
        $statusableMock->shouldReceive('hasStatus')
            //->times(1)
            ->with($item)
            ->andReturn(true);
        $statusableMock->shouldReceive('getStatus')
            //->times(1)
            ->with($item)
            ->andReturn($item->getStatus());

        return $statusableMock;
    }

    protected function getCurrentMock($item)
    {
        /** @see https://code.tutsplus.com/tutorials/mockery-a-better-way--net-28097 */
        $routeMock = Mockery::mock('Route');
        $routeMock->shouldReceive('getParam')->with('controller')->andReturn('test-controller');
        $routeMock->shouldReceive('getParam')->with('action')->andReturn('test-action');

        $currentMock = Mockery::mock('alias:Popov\\Current\\Plugin\\Current');
        $currentMock->shouldReceive('currentModule')->with($item)->andReturn('Popov\\Invoice');
        $currentMock->shouldReceive('currentRoute')->andReturn($routeMock);

        return $currentMock;
    }

    private function getFormMock()
    {
        $inputFilterMock = $this->getInputFilterMock();
        $fieldsetMock = $this->getFieldsetMock();

        $formMock = Mockery::mock('Zend\Form\FieldsetInterface, Zend\Form\FormInterface')/*->shouldIgnoreMissing()*/;
        $formMock->shouldReceive('has')->with('invoice')->andReturn(true);
        $formMock->shouldReceive('get')->with('invoice')->andReturn($fieldsetMock);
        $formMock->shouldReceive('getName')->andReturn('invoice');
        $formMock->shouldReceive('getObject')->andReturn($this->getItemMock());
        $formMock->shouldReceive('getIterator')
            ->andReturn(new \ArrayIterator(['invoice' => $fieldsetMock]));
        $formMock->shouldReceive('getInputFilter')
            ->andReturn($inputFilterMock);

        //$formMock->shouldReceive('setValidationGroup')->with(Mockery::any());

        return $formMock;
    }

    private function getInputFilterMock()
    {
        static $inputFilterMock;

        if (!$inputFilterMock) {
            $inputMock = Mockery::mock(\Zend\InputFilter\Input::class);
            $inputMock->shouldReceive('get')->with('invoice')->andReturn(Mockery::self());
            $inputMock->shouldReceive('get')->with('invoiceProducts')->andReturn(Mockery::self());
            $inputMock->shouldReceive('get')->with('quantity')->andReturn(Mockery::self());
            $inputFilterMock = Mockery::mock(\Zend\InputFilter\InputFilter::class);
            $inputFilterMock->shouldReceive('get')->with('invoice')->andReturn($inputMock);
        }

        return $inputFilterMock;
    }

    private function getFieldsetMock()
    {
        static $fieldsetMock;

        if (!$fieldsetMock) {
            $fieldsetMock = Mockery::mock('Zend\Form\Fieldset, Zend\Form\FieldsetInterface');
            $fieldsetCollection = Mockery::mock('Zend\Form\Element\Collection');
            $fieldsetCollection->shouldReceive('getName')->andReturn('invoiceProducts');
            $fieldsetCollection->shouldReceive('getTargetElement')->andReturn($fieldsetMock);
            $fieldsetMock->shouldReceive('has')->andReturnUsing(function ($arg) {
                // Mockery return true for not indexing element
                return is_string($arg) ? true : false;
            });
            $fieldsetMock->shouldReceive('get')->with('invoiceProducts')->andReturn($fieldsetCollection);
        }

        return $fieldsetMock;
    }

    public function getItemMock()
    {
        return $this->itemMock;
    }
}
