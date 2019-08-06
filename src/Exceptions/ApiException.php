<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Exceptions;

class ApiException extends \Exception
{

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @param string $message
	 * @param int $code
	 * @param string|NULL $type
	 * @param string|NULL $name
	 */
	public function __construct($message = "", $code = 0, string $type = NULL, string $name = NULL)
	{
		$this->type = $type;
		$this->name = $name;
		parent::__construct($message, $code);
	}

	/**
	 * @return string|null
	 */
	public function getType(): ?string
	{
		return $this->type;
	}

	/**
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

}
