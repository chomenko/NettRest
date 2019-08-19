<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest;

class Response extends \stdClass
{
	const ERROR_TYPE_SERVER = "server-error";
	const ERROR_TYPE_REQUEST = "request";
	const ERROR_TYPE_RESPONSE = "response";
	const ERROR_TYPE_PARAMETER = "parameter";
	const ERROR_TYPE_ATTRIBUTE = "attribute";

	/**
	 * @var int
	 */
	public $code = 200;

	/**
	 * @var array
	 */
	public $data = [];

	/**
	 * @var array
	 */
	public $errors = [];

	/**
	 * @var array
	 */
	public $messages = [];

	/**
	 * @param string $message
	 * @param string|NULL $name
	 * @param string|NULL $type
	 */
	public function addError(string $message, string $name = NULL, string $type = NULL): void
	{
		$this->errors[] = [
			"name" => $name,
			"message" => $message,
			"type" => $type,
		];
	}

	/**
	 * @return array
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}

	/**
	 * @return int
	 */
	public function getCode(): int
	{
		return $this->code;
	}

	/**
	 * @param int $code
	 */
	public function setCode(int $code): void
	{
		$this->code = $code;
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return [
			"code" => $this->code,
			"data" => $this->data,
			"errors" => $this->errors,
			"messages" => [],
		];
	}

	/**
	 * @return array
	 */
	public function getData(): array
	{
		return $this->data;
	}

	/**
	 * @param array $data
	 */
	public function setData(array $data): void
	{
		$this->data = $data;
	}

	/**
	 * @param array $data
	 */
	public function addItem(array $data): void
	{
		$this->data[] = $data;
	}

}
