<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Metadata;

class Response extends MetaHierarchy
{

	/**
	 * @var string|null
	 */
	private $description;

	/**
	 * @var array
	 */
	private $messages = [];

	/**
	 * @var array
	 */
	private $errors = [];

	/**
	 * @var bool
	 */
	private $collection = FALSE;

	/**
	 * @param Parameter $parameter
	 */
	public function addParameter(Parameter $parameter): void
	{
		$this->parameters[$parameter->getName()] = $parameter;
		if (!$parameter->getParent()) {
			$parameter->setParent($this);
		}
	}

	/**
	 * @param string $name
	 * @return Parameter|null
	 */
	public function getParameter(string $name): ?Parameter
	{
		if (array_key_exists($name, $this->parameters)) {
			return $this->parameters[$name];
		}
		return NULL;
	}

	/**
	 * @return Parameter[]
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	/**
	 * @return string|null
	 */
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 * @param string|null $description
	 */
	public function setDescription(?string $description): void
	{
		$this->description = $description;
	}

	/**
	 * @return array
	 */
	public function getMessages(): array
	{
		return $this->messages;
	}

	/**
	 * @param string $message
	 */
	public function addMessage(string $message): void
	{
		$this->messages[] = $message;
	}

	/**
	 * @return array
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}

	/**
	 * @param string $error
	 */
	public function addError(string $error): void
	{
		$this->errors[] = $error;
	}

	/**
	 * @return bool
	 */
	public function isCollection(): bool
	{
		return $this->collection;
	}

	/**
	 * @param bool $collection
	 */
	public function setCollection(bool $collection): void
	{
		$this->collection = $collection;
	}

}
