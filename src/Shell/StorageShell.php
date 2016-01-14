<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Shell;

use Cake\Console\Shell;

class StorageShell extends Shell {

/**
 * Tasks
 *
 * @var array
 */
	public $tasks = [
		'Burzum/FileStorage.Image'
	];

	public function main() {}

	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser->addSubcommand('image', [
			'help' => __('Image Processing Task.'),
			'parser' => $this->Image->getOptionParser()
		]);
		return $parser;
	}
}
