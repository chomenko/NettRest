<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\API;

abstract class BaseAnnotation implements IAnnotation
{

	/**
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		foreach ($data as $key => $value) {

			if ($key === "value") {
				continue;
			}

			$method = 'set' . str_replace('_', '', $key);
			if (!method_exists($this, $method)) {
				throw new \BadMethodCallException(sprintf('Unknown property "%s" on annotation "%s".', $key, get_class($this)));
			}
			$this->$method($value);
		}
	}

}
