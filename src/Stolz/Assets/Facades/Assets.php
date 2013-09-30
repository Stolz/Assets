<?php namespace Stolz\Assets\Facades;

use Illuminate\Support\Facades\Facade;

class Assets extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'assets'; }
}
