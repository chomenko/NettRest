<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Metadata;

class ClassStructure
{

	/**
	 * @var string
	 */
	private $class;

	/**
	 * @var Parameter[]
	 */
	private $fields = [];

	/**
	 * @var string|null
	 */
	private $groups;

	/**
	 * ClassStructure constructor.
	 * @param string $class
	 * @param array $groups
	 */
	public function __construct(string $class, array $groups)
	{
		$this->class = $class;
		$this->groups = $groups;
	}

	/**
	 * @param string $name
	 * @param Parameter $parameter
	 */
	public function addFields(string $name, Parameter $parameter)
	{
		$this->fields[$name] = $parameter;
	}

	/**
	 * @return Parameter[]
	 */
	public function getFields(): array
	{
		return $this->fields;
	}

	/**
	 * @return string|null
	 */
	public function getGroups(): ?string
	{
		return $this->groups;
	}

}
