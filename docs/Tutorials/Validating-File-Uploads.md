Validating File Uploads
=======================

You can validate your uploads by extending `Burzum\FileStorage\Storage\Listener\ValidationListener` and implement your validation methods just like you do in table objects:

```php
use Burzum\FileStorage\Storage\Listener\ValidationListener;
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

EventManager::instance()->on(new FileValidationListener());
```

This will attach the listener to the `Model.initialize()` event and add your configured validators to the FileStorage table.

References:

* [Using A Different Validation Set](http://book.cakephp.org/3.0/en/orm/validation.html#using-a-different-validation-set)
* [Table::validator()](http://api.cakephp.org/3.3/class-Cake.Validation.ValidatorAwareTrait.html#_validator)
