<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest;

use Nette\Http\Request;

class Config
{

	/**
	 * @var string|null
	 */
	private $host;

	/**
	 * Config constructor.
	 * @param array $settings
	 * @param Request $request
	 */
	public function __construct(array $settings, Request $request)
	{
		foreach (get_object_vars($this) as $name => $value) {
			if (array_key_exists($name, $settings)) {
				$this->{$name} = $settings[$name];
			}
		}

		if (!$this->host) {
			$url = $request->getUrl();
			$scheme = $url->getScheme();
			$this->host = (!empty($scheme) ? $scheme . "://" : "" ). $url->getHost();
		}
	}

	/**
	 * @return string|null
	 */
	public function getHost(): ?string
	{
		return $this->host;
	}

}
