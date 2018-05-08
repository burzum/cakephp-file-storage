<?php
namespace Burzum\FileStorage\Test\TestCase\Storage\Listener;

use Burzum\FileStorage\Model\Entity\FileStorage;
use Burzum\FileStorage\Storage\Listener\ValidationListener;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;

class TestValidationListener extends ValidationListener {

    public function validationAvatar(Validator $validator) {
        $validator->add('file', 'mimeType', [
            'rule' => ['mimeType', ['image/jpg', 'image/jpeg', 'image/png']]
        ]);

        $validator->add('file', 'imageSize', [
            'rule' => ['imageSize', [
                'height' => ['>=', 200],
                'width' => ['>=', 200]
            ]]
        ]);

        return $validator;
    }

}

class ValidationListenerTest extends TestCase {

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Burzum\FileStorage.FileStorage'
    ];

    /**
     * {@inheritDoc}
     */
    public function setUp() {
        parent::setUp();
        $this->table = TableRegistry::get('Burzum/FileStorage.FileStorage');
    }

    /**
     * testInitialize
     *
     * @return void
     */
    public function testInitialize() {
        $entity = new FileStorage([
            'model' => 'Avatar'
        ]);
        $event = new Event('Model.initialize', $this->table, [
            'entity' => $entity
        ]);
        $listener = new TestValidationListener();
        $listener->initialize($event);

        $result = $this->table->getValidator('avatar');
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
