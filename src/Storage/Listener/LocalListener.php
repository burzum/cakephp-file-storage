<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\Listener;

/**
 * Local FileStorage Event Listener for the CakePHP FileStorage plugin
 *
 * @author Florian Krämer
 * @author Tomenko Yegeny
 * @license MIT
 */
class LocalListener extends BaseListener {

	/**
	 * List of adapter classes the event listener can work with.
	 *
	 * It is used in FileStorageEventListenerBase::getAdapterClassName to get the
	 * class, to detect if an event passed to this listener should be processed or
	 * not. Only events with an adapter class present in this array will be
	 * processed.
	 *
	 * The LocalListener will ONLY work with the '\Gaufrette\Adapter\Local'
	 * adapter for backward compatiblity reasons for now. Use the BaseListener
	 * or extend this one here and add your adapter classes.
	 *
	 * @var array
	 */
	public $_adapterClasses = [
		'\Gaufrette\Adapter\Local'
	];
}
