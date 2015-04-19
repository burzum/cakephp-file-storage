Replacing Files
===============

A common task is to replace on existing image with a new image.

Assuming we have a model table called `DocumentsTable` that is associated by a`hasOne` association with the `ImageStorageTable` table. The associations alias is `Images`. Your form should look like this:

```php
echo $this->Form->file('image.file');
echo $this->Form->error('image.file');

if (isset($document) && !empty($document['Image']['id'])) {
	echo $this->Image->display($document->image);
	echo $this->Form->input('image.old_file_id', array(
		'type' => 'hidden',
		'value' => $document->id,
	));
}
```

The the trick here is the `old_file_id`. The `FileStorageTable` table, which `ImageStorageTable` extends, is checking for that field by calling `FileStorageTable::deleteOldFileOnSave()` in `FileStorageTable::afterSave()`.

So all you have to do to replace an image is to pass the `old_file_id` along with your new file data.

Just make sure that nobody can tamper your forms with unwanted data! If somebody can do that they can pass any id to delete *any* file! It is recommended to use the [Security component](http://book.cakephp.org/3.0/en/core-libraries/components/security-component.html) of the framework to avoid that.
