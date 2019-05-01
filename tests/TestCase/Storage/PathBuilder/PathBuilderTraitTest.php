<?php
declare(strict_types=1);
namespace Burzum\FileStorage\Test\TestCase\Storage\PathBuilder;

use Burzum\FileStorage\Storage\PathBuilder\BasePathBuilder;
use Burzum\FileStorage\Storage\PathBuilder\PathBuilderTrait;
use Cake\TestSuite\TestCase;

class PathBuilderTraitTest extends TestCase
{
    /**
     * Test createPathBuilder() method.
     *
     * @return void
     */
    public function testCreatePathBuilder()
    {
        $object = $this->getObjectForTrait(PathBuilderTrait::class);

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
    public function testCreatePathBuilderInvalidClass()
    {
        $object = $this->getObjectForTrait(PathBuilderTrait::class);
        $object->createPathBuilder('\stdClass');
    }

    /**
     * Test createPathBuilder() method with missing class.
     *
     * @return void
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not find path builder "Foo"!
     */
    public function testCreatePathBuilderMissingClass()
    {
        $object = $this->getObjectForTrait(PathBuilderTrait::class);
        $object->createPathBuilder('Foo');
    }

    /**
     * Test pathBuilder() method.
     *
     * @return void
     */
    public function testPathBuilder()
    {
        $object = $this->getObjectForTrait(PathBuilderTrait::class);
        $result = $object->pathBuilder('Base');
        $this->assertSame($result, $object->pathBuilder());
    }

    /**
     * Test createPathBuilder() method with missing class.
     *
     * @return void
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The first arg does not implement Burzum\FileStorage\Storage\PathBuilder\PathBuilderInterface
     */
    public function testSetInvalidPathBuilder()
    {
        $object = $this->getObjectForTrait(PathBuilderTrait::class);
        $object->SetPathBuilder(new \stdClass());
    }

    /**
     * testSetPathBuilder
     *
     * @return void
     */
    public function testSetPathBuilder()
    {
        $object = $this->getObjectForTrait(PathBuilderTrait::class);
        $object->setPathBuilder('Base');
        $builder = $object->getPathBuilder('Base');
        $this->assertInstanceOf(BasePathBuilder::class, $builder);
    }
}
