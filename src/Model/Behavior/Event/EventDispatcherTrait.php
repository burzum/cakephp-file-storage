<?php
namespace Burzum\FileStorage\Model\Behavior\Event;

use Cake\Event\EventDispatcherTrait as CakeEventDispatcherTrait;

trait EventDispatcherTrait {

	use CakeEventDispatcherTrait {
		dispatchEvent as private _dispatchEvent;
	}

/**
 * Wrapper for creating and dispatching events in a behavior.
 * It feed data with a table object.
 *
 * Returns a dispatched event.
 *
 * @param string $name Name of the event.
 * @param array|null $data Any value you wish to be transported with this event to
 * it can be read by listeners.
 * @param object|null $subject The object that this event applies to
 * ($this by default).
 *
 * @return \Cake\Event\Event
 */
	public function dispatchEvent($name, $data = null, $subject = null) {
		$data['table'] = $this->_table;
		return $this->_dispatchEvent($name, $data, $subject);
	}
}
