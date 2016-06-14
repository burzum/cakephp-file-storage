How to Use It
=============

Before you continue to read this page it is recommended that you have read about [the Storage Manager](The-Storage-Manager.md) before.

The following text is going to describe two ways to store a file. Which of both you choose depends at the end on your use case but it is recommended to use the events because they automate the whole process much more.

The basic idea of this plugin is that files are always handled as separate entities and are associated to other models. The reason for that is simple. A file has multiple properties like size, mime type and other entities in the system can have more than one file for example. It is considered as *bad* practice to store lots of file paths as reference in a table together with other data.

This plugin resolves that issue by handling each file as a completely separate entity in the application. There is just one table `file_storage` that will keep the reference to all your files, no matter where they're stored.

Preparing the File Upload
-------------------------

This section is going to show how to store a file using the Storage Manager directly.

For example you have a `reports` table and want to save a PDF to it, you would then create an association like:

```php
public function initialize(array $config)
{
	parent::initialize($config);
	$this->table('reports');

	$this->hasOne('PdfFiles', [
		'className' => 'Burzum/FileStorage.PdfFiles',
		'foreignKey' => 'foreign_key',
		'conditions' => [
			'PdfFiles.model' => 'Reports'
		]
	]);
}
```

In your `add.ctp` or `edit.ctp` views you would add something like this.

```php
echo $this->Form->create($report, ['type' => 'file']);
echo $this->Form->input('title');
echo $this->Form->file('pdf_files.file'); // Pay attention here!
echo $this->Form->input('description');
echo $this->Form->submit(__('Submit'));
echo $this->Form->end();
```

[Make sure your form is using the right HTTP method](http://book.cakephp.org/3.0/en/views/helpers/form.html#changing-the-http-method-for-a-form)!

Store an uploaded file using Events
-----------------------------------

The **FileStorage** plugin comes with a class that acts just as a listener to some of the events in this plugin. Take a look at [ImageProcessingListener.php](../../src/Event/ImageProcessingListener.php).

This class will listen to all the ImageStorage model events and save the uploaded image and then create the versions for that image and storage adapter.

It is important to understand that nearly each storage adapter requires a little different handling: Most of the time you can't treat a local file the same as a file you store in a cloud service.
The interface that this plugin and Gaufrette provide is the same but not the internals. So a path that works for your local file system might not work for your remote storage system because it has other requirements or limitations.

So if you want to store a file using Amazon S3 you would have to store it, create all the versions of that image locally and then upload each of them and then delete the local temp files. The good news is the plugin can already take care of that.

When you create a new listener it is important that you check the `model` field and the event subject object (usually a table object inheriting `\Cake\ORM\Table`) if it matches what you expect. Using the event system you could create any kind of storage and upload behavior without inheriting or touching the model code. Just write a listener class and attach it to the global EventManager.

List of events
--------------

Events triggered in the `ImageStorage` model:

 * ImageVersion.createVersion
 * ImageVersion.removeVersion
 * ImageStorage.beforeSave
 * ImageStorage.afterSave
 * ImageStorage.beforeDelete
 * ImageStorage.afterDelete

Events triggered in the `FileStorage` model:

 * FileStorage.beforeSave
 * FileStorage.afterSave
 * FileStorage.afterDelete

Event Listeners
---------------

See [this page](Included-Event-Listeners.md) for the event listeners that are included in the plugin.


Handling the File Upload Manually
---------------------------------

You'll have to customize it a little but its just a matter for a few lines.

Note the Listener expects a request data key `file` present in the form, so use `echo $this->Form->input('file');` to allow the Marshaller pick the right data from the uploaded file.

Lets go by this scenario inside the report table, assuming there is an add() method:

```php
public function add() {
	$entity = $this->newEntity($postData);
	$saved = $this->save($entity);
	if ($saved) {
		$key = 'your-file-name';
		if (StorageManager::adapter('Local')->write($key, file_get_contents($this->data['pdf_files']['file']['tmp_name']))) {
			$postData['pdf_files']['foreign_key'] = $saved->id;
			$postData['pdf_files']['model'] = 'Reports';
			$postData['pdf_files']['path'] = $key;
			$postData['pdf_files']['adapter'] = 'Local';
			$this->PdfDocuments->save($this->PdfDocuments->newEntity($postData));
		}
	}
	return $entity;
}
```

Later, when you want to delete the file, for example in the beforeDelete() or afterDelete() callback of your Report model, you'll know the adapter you have used to store the attached PdfFile and can get an instance of this adapter configuration using the StorageManager. By having the path or key available you can then simply call:

```php
StorageManager::adapter($data['PdfFile']['adapter'])->delete($data['PdfFile']['path']);
```

Insted of doing all of this in the table object that has the files associated to it you can also simply extend the FileStorage table from the plugin and add your storage logic there and use that table for your association.

Why is it done like this?
-------------------------

Every developer might want to store the file at a different point or apply other operations on the file before or after it is stored. Based on different circumstances you might want to save an associated file even before you created the record its going to get attached to, in other scenarios like in this documentation you might want to do it after.

The ``$key`` is also a key aspect of it: Different adapters might expect a different key. A key for the Local adapter of Gaufrette is usually a path and a file name under which the data gets stored. That's also the reason why you use `file_get_contents()` instead of simply passing the tmp path as it is.

It is up to you how you want to generate the key and build your path. You can customize the way paths and file names are build by writing a custom event listener for that.

It is highly recommended to read the Gaufrette documentation for the read() and write() methods of the adapters.
