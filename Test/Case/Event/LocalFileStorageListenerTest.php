<?php
App::uses('CakeEvent', 'Event');
App::uses('FileStorage', 'FileStorage.Model');
App::uses('LocalFileStorageListener', 'FileStorage.Event');
App::uses('FileStorageTestCase', 'FileStorage.TestSuite');

/**
 * LocalFileStorageListenerTest Test
 *
 * @author Florian Krämer
 * @copyright 2012 Florian Krämer
 * @license MIT
 */
class LocalFileStorageListenerTest extends FileStorageTestCase {

/**
 * setUp
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Model = new FileStorage();
		$this->Listener = new LocalFileStorageListener();
	}

/**
 * tearDown
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Listener, $this->Model);
		ClassRegistry::flush();
	}

/**
 * testAfterSave
 *
 * @return void
 */
	public function testAfterSave() {
		$data = [
			'FileStorage' => [
				'id' => 'file-storage-3',
				'user_id' => 'user-1',
				'foreign_key' => 'item-1',
				'model' => 'Item',
				'filename' => 'titus-bienebek-bridle.jpg',
				'filesize' => '',
				'mime_type' => 'image/jpg',
				'extension' => 'jpg',
				'hash' => '',
				'path' => '',
				'adapter' => 'Local',
				'created' => '2012-01-01 12:00:00',
				'modified' => '2012-01-01 12:00:00',
				'file' => [
					'error' => 0,
					'tmp_name' => CakePlugin::path('FileStorage') . DS . 'Test' . DS . 'Fixture' . DS . 'File' . DS . 'titus.jpg',
					'name' => 'titus.jpg'
				]
			]
		];

		$this->Model->data = $data;

		$event = new CakeEvent('Model.afterSave', $this->Model, [
			'storage' => StorageManager::adapter('Local'),
			'record' => $data
		]);
		$this->Listener->afterSave($event);
		$expected = 'files' . DS . 'Item' . DS . '35' . DS . '20' . DS . '80' . DS . 'filestorage3' . DS;
		$this->assertEquals($expected, $event->result['FileStorage']['path']);
	}
}