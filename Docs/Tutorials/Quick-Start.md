Quick-Start
===========

```js
{
	"config": {
		"vendor-dir": "app/Vendor/",
		"preferred-install": "source"
	},
	"require": {
		"cakedc/migrations": "dev-master",
		"knplabs/gaufrette": "dev-master",
		"imagine/imagine": "dev-master"
	},
	"extra": {
		"installer-paths": {
			"app/Plugin/FileStorage": ["burzum/FileStorage"],
			"app/Plugin/Imagine": ["burzum/Imagine"]
		}
	}
}
```