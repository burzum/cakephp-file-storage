Quick-Start Tutorial
====================

In your applications `config/bootstrap.php` load the plugin:

```php
PLugin::load('Burzum/FileStorage', [
	'bootstrap' => true
]);
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

We now *assume* that you have a table called `Users` and that you want to attach an avatar to your users.

In your `App\Model\Table\UsersTable.php` file is a method called `inititalize()`. Add the avatar file assocation there:

```php
$this->hasOne('Avatars', [
	'className' => 'Burzum/FileStorage.FileStorage',
	'foreignKey' => 'foreign_key',
	'conditions' => [
		'Avatars.model' => 'Avatar'
	]
]);
```

Especially pay attention to the `conditions` key in the config array of the association. You must specify this here or File Storage won't be able to identify that kind of file properly.

You now have **two** possible ways of storing the file:
 
Either save it through the association along with your users save call or save it separate. However, whatever you do, it is important that you set the `foreign_key` and `model` field for the associated file storage entity.

If you don't specify the model field it will use the file storage tables table name by default and your has one association won't find it.

```php
class UsersController {

	// Edit the current logged in user
	// Requires the Auth Component!
	public function edit() {
		$user = $this->Users->get($this->Auth->user('id'));

		if ($this->request->is(['post', 'put'])) {
			$user = $this->Users->patchEntity($user, $this->request->data());
			if (!empty($users->avatar->file)) {
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
