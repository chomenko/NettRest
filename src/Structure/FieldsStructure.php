<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Structure;

abstract class FieldsStructure implements IStructure
{

	/**
	 * @var Method
	 */
	protected $method;

	/**
	 * @var string|null
	 */
	protected $description;

	/**
	 * @var bool
	 */
	protected $collection = FALSE;

	/**
	 * @var array
	 */
	protected $groups = [];

	/**
	 * @var Field[]
	 */
	protected $fields = [];

	/**
	 * @var string|null
	 */
	protected $class;

	/**
	 * StructureRequest constructor.
	 * @param Method $method
	 */
	public function __construct(Method $method)
	{
		$this->method = $method;
	}

	/**
	 * @return Method
	 */
	public function getParent()
	{
		return $this->method;
	}

	/**
	 * @param string $name
	 * @param IStructure $structure
	 */
	public function addField(string $name, IStructure $structure): void
	{
		$this->fields[$name] = $structure;
	}

	/**
	 * @return IStructure[]
	 */
	public function getFields(): array
	{
		return $this->fields;
	}

	/**
	 * @param string $name
	 * @return Field|null
	 */
	public function getField(string $name): ?IStructure
	{
		return array_key_exists($name, $this->fields) ? $this->fields[$name] : NULL;
	}

	/**
	 * @return Method
	 */
	public function getMethod(): Method
	{
		return $this->method;
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

	/**
	 * @return array
	 */
	public function getGroups(): array
	{
		return $this->groups;
	}

	/**
	 * @param array $groups
	 */
	public function setGroups(array $groups): void
	{
		$this->groups = $groups;
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

}
