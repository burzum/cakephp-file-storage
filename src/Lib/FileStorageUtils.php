<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Lib;

use Burzum\FileStorage\Storage\StorageUtils;
use Cake\Filesystem\File;

/**
 * Utility methods for which I could not find a better place
 *
 * @deprecated Use \Burzum\FileStorage\Storage\StorageUtils instead.
 */
class FileStorageUtils {

/**
 * Return file extension from a given filename
 *
 * @param string $name
 * @param boolean $realFile
 * @link http://php.net/manual/en/function.pathinfo.php
 * @return false|string string or false
 */
	public static function fileExtension($name, $realFile = false) {
		return StorageUtils::fileExtension($name, $realFile);
	}

/**
 * Builds a semi-random path based on a given string to avoid having thousands of files
 * or directories in one directory. This would result in a slowdown on most file systems.
 *
 * Works up to 5 level deep
 *
 * @deprecated Use the randomPath() method from the BasePathBuilder instead.
 * @link https://www.box.com/blog/crc32-checksums-the-good-the-bad-and-the-ugly/
 * @throws InvalidArgumentException
 * @param mixed $string
 * @param integer $level 1 to 5
 * @return null|string
 */
	public static function randomPath($string, $level = 3) {
		return StorageUtils::randomPath($string, $level);
	}

/**
 * Helper method to trim last trailing slash in file path
 *
 * @param string $path Path to trim
 * @return string Trimmed path
 */
	public static function trimPath($path) {
		return StorageUtils::trimPath($path);
	}

/**
 * Converts windows to linux pathes and vice versa
 *
 * @param string
 * @return string
 */
	public static function normalizePath($string) {
		return StorageUtils::normalizePath($string);
	}

/**
 * Method to normalize the annoying inconsistency of the $_FILE array structure
 *
 * @link http://www.php.net/manual/en/features.file-upload.multiple.php#109437
 * @param array $array
 * @return array Empty array if $_FILE is empty, if not normalize array of Filedata.{n}
 */
	public static function normalizeGlobalFilesArray($array = null) {
		return StorageUtils::normalizeGlobalFilesArray($array);
	}

/**
 * Serializes and then hashes an array of operations that are applied to an image
 *
 * @param array $operations
 * @return string
 */
	public static function hashOperations($operations) {
		return StorageUtils::hashOperations($operations);
	}

/**
 * Generates the hashes for the different image version configurations.
 *
 * @param string|array $configPath
 * @return array
 */
	public static function generateHashes($configPath = 'FileStorage') {
		return StorageUtils::generateHashes($configPath);
	}

/**
 * Recursive ksort() implementation
 *
 * @param array $array
 * @param integer
 * @return string
 * @link https://gist.github.com/601849
 */
	public static function ksortRecursive(&$array, $sortFlags) {
		return StorageUtils::getFileHash($array, $sortFlags);
	}

/**
 * Returns an array that matches the structure of a regular upload for a local file
 *
 * @param $file
 * @param string File with path
 * @return array Array that matches the structure of a regular upload
 */
	public static function uploadArray($file, $filename = null) {
		return StorageUtils::uploadArray($file, $filename);
	}

/**
 * Gets the hash of a file.
 *
 * You can use this to compare if you got two times the same file uploaded.
 *
 * @param string $file Path to the file on your local machine.
 * @param string $method 'md5' or 'sha1'
 * @throws \InvalidArgumentException
 * @link http://php.net/manual/en/function.md5-file.php
 * @link http://php.net/manual/en/function.sha1-file.php
 * @link http://php.net/manual/en/function.sha1-file.php#104748
 * @return string
 */
	public static function getFileHash($file, $method = 'sha1') {
		return StorageUtils::getFileHash($file, $method);
	}
}
