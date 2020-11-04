<?php

declare(strict_types = 1);

namespace Burzum\FileStorage\Shell;

use Burzum\FileStorage\Utility\StorageUtils;
use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Core\Configure;

/**
 * Class StorageShell
 *
 * @property \Burzum\FileStorage\Shell\Task\ImageTask $Image
 */
class StorageShell extends Shell
{
    /**
     * Tasks
     *
     * @var array
     */
    public $tasks = [
        'Burzum/FileStorage.Image',
    ];

    /**
     * @inheritDoc
     */
    public function getOptionParser(): ConsoleOptionParser
    {
        $parser = parent::getOptionParser();
        $parser->addOption('adapter', [
            'short' => 'a',
            'help' => __('The adapter config name to use.'),
            'default' => 'Local',
        ]);
        $parser->addOption('identifier', [
            'short' => 'i',
            'help' => __('The file identifier (`model` field in `file_storage` table).'),
            'default' => null,
        ]);
        $parser->addOption('model', [
            'short' => 'm',
            'help' => __('The model / table to use.'),
            'default' => 'Burzum/FileStorage.FileStorage',
        ]);
        $parser->addSubcommand('image', [
            'help' => __('Image Processing Task.'),
            'parser' => $this->Image->getOptionParser(),
        ]);
        $parser->addSubcommand('store', [
            'help' => __('Stores a file in the DB.'),
        ]);
        $parser->addSubcommand('attach', [
            'help' => __('Attach a file to a record.'),
        ]);

        return $parser;
    }

    /**
     * Does the arg and params checks for store().
     *
     * @return void
     */
    protected function _storePreCheck(): void
    {
        if (empty($this->args[0])) {
            $this->abort('No file provided!');
        }

        if (!file_exists($this->args[0])) {
            $this->abort('The file does not exist!');
        }

        /** @var \Phauthentic\Infrastructure\Storage\FileStorage|null $storage */
        $storage = Configure::read('FileStorage.behaviorConfig.fileStorage');
        if (!$storage) {
            $this->abort(sprintf('Invalid adapter config `%s` provided!', $this->params['adapter']));
        }
        //$adapter = $storage->getStorage($this->params['adapter']);
    }

    /**
     * Store a local file via command line in any storage backend.
     *
     * @return void
     */
    public function store(): void
    {
        $this->_storePreCheck();
        $model = $this->loadModel($this->params['model']);
        if (Configure::read('App.uploadedFilesAsObjects', true)) {
            $fileData = StorageUtils::fileToUploadedFileObject($this->args[0]);
        } else {
            $fileData = StorageUtils::fileToUploadedFileArray($this->args[0]);
        }
        $entity = $model->newEntity([
            'adapter' => $this->params['adapter'],
            'file' => $fileData,
            'filename' => is_array($fileData) ? $fileData['name'] : $fileData->getClientFilename(),
        ]);
        if ($entity->getErrors()) {
            $this->abort('Validation failed: ' . print_r($entity->getErrors(), true));
        }

        if (!$model->save($entity)) {
            $this->abort('Failed to save the file.');
        }

        $this->out('File successfully saved!');
        $this->out('ID:   ' . $entity->get('id'));
        $this->out('Path: ' . $entity->get('path'));
        $this->out('Size: ' . $entity->get('filesize'));
    }

    /**
     * Store a local file via command line in any storage backend.
     *
     * @return void
     */
    public function attach(): void
    {
        $this->_storePreCheck();
        $model = $this->loadModel($this->params['model']);
        if (Configure::read('App.uploadedFilesAsObjects', true)) {
            $fileData = StorageUtils::fileToUploadedFileObject($this->args[0]);
        } else {
            $fileData = StorageUtils::fileToUploadedFileArray($this->args[0]);
        }
        $entity = $model->newEntity([
            'adapter' => $this->params['adapter'],
            'file' => $fileData,
            'filename' => is_array($fileData) ? $fileData['name'] : $fileData->getClientFilename(),
            'model' => 'X',
            'foreign_key' => '1',
        ]);
        if ($entity->getErrors()) {
            $this->abort('Validation failed: ' . print_r($entity->getErrors(), true));
        }

        if (!$model->save($entity)) {
            $this->abort('Failed to save the file.');
        }

        $this->out('File successfully attached to record ``!');
        $this->out('ID:   ' . $entity->get('id'));
        $this->out('Path: ' . $entity->get('path'));
        $this->out('Size: ' . $entity->get('filesize'));
    }
}
