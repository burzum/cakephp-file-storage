<?php
declare(strict_types = 1);

namespace Burzum\FileStorage\Shell\Task;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Datasource\ResultSetInterface;
use Cake\Event\Event;
use Cake\Event\EventDispatcherTrait;
use Cake\Event\EventManager;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Phauthentic\Infrastructure\Storage\Exception\StorageException;

/**
 * Task to generate and remove image versions based on the identifier and the versions.
 *
 * The identifier is the "model" field from the storage table. Versions is a comma
 * separated list of image versions configured for the given identifier.
 *
 * bin\cake Burzum/FileStorage.storage image remove <identifier> <versions>
 * bin\cake Burzum/FileStorage.storage image remove ProfilePicture "thumb60, crop50"
 */
class ImageTask extends Shell
{
    use EventDispatcherTrait;

    /**
     * @var \Cake\ORM\Table
     */
    protected $Table;

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Table = TableRegistry::getTableLocator()->get('Burzum/FileStorage.ImageStorage');
    }

    /**
     * Remove image versions.
     *
     * @return void
     */
    public function remove(): void
    {
        $this->_loop($this->args[0], explode(',', $this->args[1]), 'remove');
    }

    /**
     * Create image versions.
     *
     * @return void
     */
    public function generate(): void
    {
        $this->_loop($this->args[0], explode(',', $this->args[1]), 'generate');
    }

    /**
     * Loops through image records and performs requested operations on them.
     *
     * @param string $identifier
     * @param array $options
     * @param string $action
     *
     * @return void
     */
    protected function _loop(string $identifier, $options, $action): void
    {
        $count = $this->_getCount($identifier);
        $offset = 0;
        $limit = $this->params['limit'];

        $this->out(__d('file_storage', '{0} record(s) will be processed.' . "\n", $count));

        do {
            $records = $this->_getRecords($identifier, $limit, $offset);
            if ($records->count()) {
                foreach ($records as $record) {
                    $method = '_' . $action . 'Image';
                    try {
                        $this->{$method}($record, $options);
                    } catch (StorageException $e) {
                        $this->err($e->getMessage());
                    }
                }
            }
            $offset += $limit;
            $this->out(__d('file_storage', '{0} of {1} records processed.', [$limit, $count]));
        } while ($records->count() > 0);
    }

    /**
     * Triggers the event to remove image versions.
     *
     * @param \Cake\ORM\Entity $record
     * @param array $options
     *
     * @return void
     */
    protected function _removeImage($record, $options): void
    {
        $Event = new Event('ImageVersion.removeVersion', $this->Table, [
            'entity' => $record,
            'operations' => $options,
        ]);
        EventManager::instance()->dispatch($Event);
    }

    /**
     * Triggers the event to generate the new images.
     *
     * @param \Cake\ORM\Entity $record
     * @param array $options
     *
     * @return void
     */
    protected function _generateImage($record, $options): void
    {
        $Event = new Event('ImageVersion.createVersion', $this->Table, [
            'entity' => $record,
            'operations' => $options,
        ]);
        EventManager::instance()->dispatch($Event);
    }

    /**
     * Gets the records for the loop.
     *
     * @param string $identifier Identifier.
     * @param int $limit Records limit.
     * @param int $offset Records offset.
     *
     * @return \Cake\Datasource\ResultSetInterface
     */
    public function _getRecords(string $identifier, int $limit, int $offset): ResultSetInterface
    {
        return $this->Table
            ->find()
            ->where([$this->Table->getAlias() . '.model' => $identifier])
            ->limit($limit)
            ->offset($offset)
            ->all();
    }

    /**
     * Gets the amount of records for an identifier in the DB.
     *
     * @param string $identifier
     *
     * @return int
     */
    protected function _getCount(string $identifier): int
    {
        $count = $this->_getCountQuery($identifier)->count();
        if ($count === 0) {
            $this->out(__d('file_storage', 'No records for identifier "{0}" found.', $identifier));
            $this->_stop();
        }

        return $count;
    }

    /**
     * Gets the query object for the count.
     *
     * @param string $identifier
     *
     * @return \Cake\ORM\Query
     */
    protected function _getCountQuery(string $identifier): Query
    {
        return $this->Table
            ->find()
            ->where([
                $this->Table->getAlias() . '.model' => $identifier,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getOptionParser(): ConsoleOptionParser
    {
        $parser = parent::getOptionParser();
        $parser->addOption('model', [
            'short' => 'm',
            'help' => __('The model to use.'),
            'default' => 'Burzum/FileStorage.ImageStorage',
        ]);

        $parser->addOption('limit', [
            'short' => 'l',
            'help' => __('The limit of records to process in a batch.'),
            'default' => 50,
        ]);

        $parser->addArguments([
            'identifier' => ['help' => 'The identifier to process', 'required' => true],
            'versions' => ['help' => 'The versions to process', 'required' => true],
        ]);

        return $parser;
    }
}
