Quick-Start Tutorial
====================

It is required that you have at least a basic understanding of how the event system of CakePHP work works. If you're unsure it is recommended to read about it first. It is expected that you take the time to try to actually *understand* what you're doing instead of just copy and pasting the code. Understanding OOP and namespaces in php is required for this tutorial.

This tutorial will assume that we're going to add an avatar image upload for our users.

For image processing you'll need the Imagine plugin. If you don't have it already added, add it now:

```sh
composer require burzum/cakephp-imagine-plugin
```

In your applications `config/bootstrap.php` load the plugins:

```php
Plugin::load('Burzum/FileStorage', [
	'bootstrap' => true
]);
Plugin::load('Burzum/Imagine');
```

This will load the `bootstrap.php` of the File Storage plugin. The default configuration in there will load the LocalStorage listener and the ImageProcessing listener. You can also skip that bootstrap part and configure your own listeners in your apps bootstrap.php or a new file.

To make image processing work you'll have to add this to your applications bootstrap.php as well:

```php
/**
 * Image resizing configuration
 */
Configure::write('FileStorage', array(
	'imageSizes' => [
		'Avatar' => [
			'crop180' => [
				'squareCenterCrop' => [
					'size' => 180
				]
			],
			'crop100' => [
				'squareCenterCrop' => [
					'size' => 100
				]
			],
			'crop40' => [
				'squareCenterCrop' => [
					'size' => 40
				]
			]
		]
	]
));
```

We now assume that you have a table called `Users` and that you want to attach an avatar image to your users.

In your `App\Model\Table\UsersTable.php` file is a method called `inititalize()`. Add the avatar file assocation there:

```php
$this->hasOne('Avatars', [
	'className' => 'Burzum/FileStorage.FileStorage',
	'foreignKey' => 'foreign_key',
	'conditions' => [
		'Avatars.model' => 'Users'
	]
]);
```

Especially pay attention to the `conditions` key in the config array of the association. You must specify this here or File Storage won't be able to identify that kind of file properly.

Either save it through the association along with your users save call or save it separate. However, whatever you do, it is important that you set the `foreign_key` and `model` field for the associated file storage entity.

If you don't specify the model field it will use the file storage tables table name by default and your has one association won't find it.

Inside the `edit.ctp` view file of your users edit method:

```php
echo $this->Form->create($user);
echo $this->Form->input('username');
// That's the important line / field
echo $this->From->file('avatar.file');
echo $this->Form->submit(__('Submit'));
echo $this->Form->end();
```

You **must** use the `file` field for the uploaded file. The plugin will check the entity for this field.

Your users controller `edit()` method:

```php
/**
 * Assuming you've loaded:
 *
 * - AuthComponent
 * - FlashComponent
 */
class UsersController extends AppController {

	public function edit() {
		$userId = $this->Auth->user('id');
		$user = $this->Users->get($userId);

		if ($this->request->is(['post', 'put'])) {
			$user = $this->Users->patchEntity($user, $this->request->data());
			if (!empty($users->avatar->file)) {
				$users->avatar->set('user_id', $userId); // Optional
				$users->avatar->set('model', 'Avatars');
			}

			if ($this->Users->save($user)) {
				$this->Flash->success('User saved');
			} else {
				$this->Flash->error('There was a problem saving the user.');
			}
		}

		$this->set('user', $user);
	}
}
```
