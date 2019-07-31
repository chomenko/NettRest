<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest;

class Config
{

	/**
	 * @var array
	 */
	private $mappings = [];

	/**
	 * @var string
	 */
	private $module = "Api";

	/**
	 * @var string|null
	 */
	private $host;

	/**
	 * Config constructor.
	 * @param array $settings
	 */
	public function __construct(array $settings)
	{
		foreach (get_object_vars($this) as $name => $value) {
			if (array_key_exists($name, $settings)) {
				$this->{$name} = $settings[$name];
			}
		}
	}

	/**
	 * @return array
	 */
	public function getMappings(): array
	{
		return $this->mappings;
	}

	/**
	 * @return string
	 */
	public function getModule(): string
	{
		return $this->module;
	}

	/**
	 * @return string|null
	 */
	public function getHost(): ?string
	{
		return $this->host;
	}

}
