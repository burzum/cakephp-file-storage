<?php
namespace Burzum\FileStorage\Validation;

trait ImageSizeValidationTrait {

	/**
	 * Image size validation method
	 *
	 * @param mixed $check
	 * @param array $options is an array with key width or height and a value of array
	 *    with two options, operator and value. For example:
	 *    array('height' => array('==', 100)) will only be true if the image has a
	 *    height of exactly 100px. See the CakePHP core class and method
	 *    Validation::comparison for all operators.
	 * @return boolean true
	 * @see Validation::comparison()
	 * @throws \InvalidArgumentException
	 */
	public function validateImageSize($check, array $options = []) {
		if (!isset($options['height']) && !isset($options['width'])) {
			throw new \InvalidArgumentException('Missing image size validation options! You must provide a height and / or width.');
		}

		$imageFile = $this->_extractTmpFile($check);

		list($width, $height) = getimagesize($imageFile);
		$height = $this->_validateImageSize('height', $height, $options);
		$width = $this->_validateImageSize('width', $width, $options);

		if ($height === false || $width === false) {
			return false;
		}

		return true;
	}

	/**
	 * Extract the image file from the check.
	 *
	 * @param strong|array $check
	 * @return string
	 */
	protected function _extractTmpFile($check) {
		if (is_string($check)) {
			return $check;
		}

		$check = array_values($check);
		$check = $check[0];

		if (is_array($check) && isset($check['tmp_name'])) {
			return $check['tmp_name'];
		}

		return $check;
	}

	/**
	 * Validates the image size
	 *
	 * @param string $widthOrHeight
	 * @param int $size
	 * @param array $options
	 * @return bool
	 */
	protected function _validateImageSize($widthOrHeight, $size, $options) {
		if (isset($options[$widthOrHeight])) {
			return Validation::comparison($size, $options[$widthOrHeight][0], $options[$widthOrHeight][1]);
		}

		return true;
	}

}
