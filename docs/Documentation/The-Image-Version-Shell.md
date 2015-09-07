The Image Version Shell
=======================

The shell comes with three pretty much self explaining commands: `generate`, `remove` and `regenerate`.

Generate
--------

**Arguments**

* **model (required)**: Value of the model property of the images to generate.
* **version (required)**: Image version to generate.

**Options**

* **storageTable**: The storage table for image processing you want to use.
* **limit**: Limits the amount of records to be processed in one batch.
* **keep-old-versions**: Use this switch if you do not want to overwrite existing versions.

Example:

```sh
bin\cake imageVersion generate Avatar t150
```

Remove
------

**Arguments**

* **model (required)**: Value of the model property of the images to generate.
* **version (required)**: Image version to generate.

**Options**

* **storageTable**: The storage table for image processing you want to use.
* **limit**: Limits the amount of records to be processed in one batch.

Example:

```sh
bin\cake imageVersion remove Avatar t150
```

Regenerate
----------

**Arguments**

* **model (required)**: Value of the model property of the images to generate.

**Options**

* **storageTable**: The storage table for image processing you want to use.
* **limit**: Limits the amount of records to be processed in one batch.
* **keep-old-versions**: Use this switch if you do not want to overwrite existing versions.

Example:

```sh
bin\cake imageVersion regenerate Avatar
```
