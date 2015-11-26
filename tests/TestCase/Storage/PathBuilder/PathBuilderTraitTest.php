<?php
namespace Burzum\FileStorage\Test\TestCase\Storage\PathBuilder;

use Cake\TestSuite\TestCase;

class PathBuilderTraitTest extends TestCase {

/**
 * Test createPathBuilder() method.
 *
 * @return void
 */
	public function testCreatePathBuilder() {
		$object = $this->getObjectForTrait('Burzum\FileStorage\Storage\PathBuilder\PathBuilderTrait');

		$pathBuilder = $object->createPathBuilder('Base');
		$this->assertInstanceOf('Burzum\FileStorage\Storage\PathBuilder\PathBuilderInterface', $pathBuilder);
		$this->assertInstanceOf('Burzum\FileStorage\Storage\PathBuilder\BasePathBuilder', $pathBuilder);
	}

/**
 * Test createPathBuilder() method with invalid class.
 *
 * @return void
 * @expectedException RuntimeException
 * @expectedExceptionMessage Path builder class "\stdClass" does not implement the PathBuilderInterface interface!
 */
	public function testCreatePathBuilderInvalidClass() {
		$object = $this->getObjectForTrait('Burzum\FileStorage\Storage\PathBuilder\PathBuilderTrait');
		$object->createPathBuilder('\stdClass');
	}

/**
 * Test createPathBuilder() method with missing class.
 *
 * @return void
 * @expectedException RuntimeException
 * @expectedExceptionMessage Could not find path builder "Foo"!
 */
	public function testCreatePathBuilderMissingClass() {
		$object = $this->getObjectForTrait('Burzum\FileStorage\Storage\PathBuilder\PathBuilderTrait');
		$object->createPathBuilder('Foo');
	}

/**
 * Test pathBuilder() method.
 *
 * @return void
 */
	public function testPathBuilder() {
		$object = $this->getObjectForTrait('Burzum\FileStorage\Storage\PathBuilder\PathBuilderTrait');
		$pathBuilder = $this->getMock('Burzum\FileStorage\Storage\PathBuilder\PathBuilderInterface');
		$result = $object->pathBuilder('Base');
		$this->assertSame($result, $object->pathBuilder());
	}
}
