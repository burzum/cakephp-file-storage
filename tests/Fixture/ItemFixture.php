<?php

declare(strict_types = 1);

namespace Burzum\FileStorage\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ItemFixture extends TestFixture
{
    /**
     * Name
     *
     * @var string
     */
    public $name = 'Item';

    /**
     * Table
     *
     * @var string
     */
    public $table = 'items';

    /**
     * Fields
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer', 'null' => true, 'default' => null, 'autoIncrement' => true],
        'name' => ['type' => 'string', 'null' => true, 'default' => null],
        'path' => ['type' => 'string', 'null' => true, 'default' => null],
        'filename' => ['type' => 'string', 'null' => true, 'default' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'name' => 'Cake',
        ],
        [
            'name' => 'More Cake',
        ],
        [
            'name' => 'A lot Cake',
        ],
    ];
}
