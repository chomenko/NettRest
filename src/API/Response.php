<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\API;

use Chomenko\NettRest\API\BaseAnnotation;
use Chomenko\NettRest\API\Parameter;
use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Response extends BaseAnnotation
{

	/**
	 * @var string|null
	 */
	private $description;

	/**
	 * @var string|null
	 */
	private $class;

	/**
	 * @var bool
	 */
	private $collection = FALSE;

	/**
	 * @var string|null
	 */
	private $name;

	/**
	 * @var array|null
	 */
	private $groups;

	/**
	 * @var array
	 */
	private $items = [];

	/**
	 * @var array
	 */
	private $errors = [];

	/**
	 * @var array
	 */
	private $messages = [];

	/**
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		if (isset($data["value"])) {
			$this->setClass($data["value"]);
		}
		parent::__construct($data);
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
	 * @return string|null
	 */
	public function getClass(): ?string
	{
		return $this->class;
	}

	/**
	 * @param string|null $class
	 */
	public function setClass(?string $class): void
	{
		$this->class = $class;
	}

	/**
	 * @return array|null
	 */
	public function getGroups(): ?array
	{
		return $this->groups;
	}

	/**
	 * @param array|null $groups
	 */
	public function setGroups(?array $groups): void
	{
		$this->groups = $groups;
	}

	/**
	 * @return array
	 */
	public function getItems(): array
	{
		return $this->items;
	}

	/**
	 * @param array $items
	 */
	public function setItems(array $items): void
	{
		$this->items = $items;
	}

	/**
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * @param string|null $name
	 */
	public function setName(?string $name): void
	{
		$this->name = $name;
	}

	/**
	 * @return array
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}

	/**
	 * @param array $errors
	 */
	public function setErrors(array $errors): void
	{
		$this->errors = $errors;
	}

	/**
	 * @param string $error
	 */
	public function addErrors(string $error): void
	{
		$this->errors[] = $error;
	}

	/**
	 * @return array
	 */
	public function getMessages(): array
	{
		return $this->messages;
	}

	/**
	 * @param array $messages
	 */
	public function setMessages(array $messages): void
	{
		$this->messages = $messages;
	}

	/**
	 * @param string $message
	 */
	public function addMessages(string $message): void
	{
		$this->messages = $message;
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
