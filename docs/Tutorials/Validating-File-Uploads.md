Validating File Uploads
=======================

You can validate

```php
use Burzum\FileStorage\Storage\Listener\ValidationListener;
use Cake\Validation\Validator;

class FileValidationListener extends ValidationListener {

	public function avatarValidator(Validator $validator) {
		// Your validation rules here
	}
}

EventManager::instance()->on(new FileValidationListener());
```

(validator())[http://api.cakephp.org/3.3/class-Cake.Validation.ValidatorAwareTrait.html#_validator]
