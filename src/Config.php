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
	 * @var string
	 */
	private $brand = "NettRest";

	/**
	 * @var string
	 */
	private $title = "RESTful API";

	/**
	 * @var string
	 */
	private $logo = __DIR__ . "/Module/assets/nettrest_logo.png";

	/**
	 * @var array
	 */
	private $assets = [
		"js" => [
			__DIR__ . "/Module/assets/libs/jquery-3.2.1/jquery.min.js",
			__DIR__ . "/Module/assets/libs/bootstrap-4/bootstrap.min.js",
			__DIR__ . "/Module/assets/libs/ace/ace.js",
			__DIR__ . "/Module/assets/libs/ace/worker-json.js",
			__DIR__ . "/Module/assets/libs/ace/mode/mode-json.js",
			__DIR__ . "/Module/assets/libs/ace/theme/theme-monokai.js",
			__DIR__ . "/Module/assets/script.js",
		],
		"css" => [
			__DIR__ . "/Module/assets/libs/bootstrap-4/bootstrap.min.css",
			__DIR__ . "/Module/assets/style.css",
		],
	];

	/**
	 * Config constructor.
	 * @param array $settings
	 * @param Request $request
	 */
	public function __construct(array $settings, Request $request)
	{
		foreach (get_object_vars($this) as $name => $value) {
			if (array_key_exists($name, $settings)) {
				$value = $settings[$name];
				if (is_array($this->{$name}) && is_array($value)) {
					$value = array_merge_recursive($this->{$name}, $value);
				}
				$this->{$name} = $value;
			}
		}

		if (!$this->host) {
			$url = $request->getUrl();
			$scheme = $url->getScheme();
			$this->host = (!empty($scheme) ? $scheme . "://" : "" ) . $url->getHost();
		}
	}

	/**
	 * @return string|null
	 */
	public function getHost(): ?string
	{
		return $this->host;
	}

	/**
	 * @return string
	 */
	public function getBrand(): string
	{
		return $this->brand;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->title;
	}

	/**
	 * @return string
	 */
	public function getLogo(): string
	{
		return $this->logo;
	}

	/**
	 * @return array
	 */
	public function getAssets(): array
	{
		return $this->assets;
	}

}
