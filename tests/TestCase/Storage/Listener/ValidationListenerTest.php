<?php
declare(strict_types=1);

namespace Burzum\FileStorage\Test\TestCase\Storage\Listener;

use Burzum\FileStorage\Model\Entity\FileStorage;
use Burzum\FileStorage\Storage\Listener\ValidationListener;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;

class TestValidationListener extends ValidationListener
{
    public function validationAvatar(Validator $validator)
    {
        $validator->add('file', 'mimeType', [
            'rule' => ['mimeType', ['image/jpg', 'image/jpeg', 'image/png']],
        ]);

        $validator->add('file', 'imageSize', [
            'rule' => ['imageSize', [
                'height' => ['>=', 200],
                'width' => ['>=', 200],
            ]],
        ]);

        return $validator;
    }
}

/**
 * ValidationListenerTest
 */
class ValidationListenerTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Burzum\FileStorage.FileStorage',
    ];

    /**
     * File Storage Table
     *
     * @var \Burzum\FileStorage\Model\Table\FileStorageTable
     */
    public $FileStorage;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->FileStorage = TableRegistry::getTableLocator()->get('Burzum/FileStorage.FileStorage');
    }

    /**
     * testInitialize
     *
     * @return void
     */
    public function testInitialize()
    {
        $entity = new FileStorage([
            'model' => 'Avatar',
        ]);
        $event = new Event('Model.initialize', $this->FileStorage, [
            'entity' => $entity,
        ]);
        $listener = new TestValidationListener();
        $listener->initialize($event);

        $result = $this->FileStorage->getValidator('avatar');
        $this->assertInstanceOf(Validator::class, $result);

        $mockListener = $this->getMockBuilder(TestValidationListener::class)
            ->setMethods(['validationAvatar'])
            ->getMock();

        $mockListener->expects($this->at(0))
            ->method('validationAvatar')
            ->will($this->returnValue(new Validator()));

        $mockListener->initialize($event);
    }
}
