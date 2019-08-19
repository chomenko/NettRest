<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Structure;

use Nette\Utils\Html;
use Symfony\Component\Validator\Constraint;

class Parameter
{

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string|null
	 */
	private $description;

	/**
	 * @var mixed
	 */
	private $example;

	/**
	 * @var Constraint[]
	 */
	private $rules = [];

	/**
	 * @var array
	 */
	private $groups = [];

	/**
	 * @var bool
	 */
	private $required = FALSE;

	/**
	 * @var string|null
	 */
	private $type;

	/**
	 * @var string|null
	 */
	private $typeClass;

	/**
	 * @var string|null
	 */
	private $declareClass;

	/**
	 * @var string|null
	 */
	private $declareProperty;

	/**
	 * @var bool
	 */
	protected $collection = FALSE;

	/**
	 * Parameter constructor.
	 * @param string $name
	 */
	public function __construct(string $name)
	{
		$this->name = $name;
	}

	/**
	 * @return Constraint[]
	 */
	public function getRules(): array
	{
		return $this->rules;
	}

	/**
	 * @param Constraint $rules
	 */
	public function addRule(Constraint $rules): void
	{
		$this->rules[] = $rules;
	}

	/**
	 * @return mixed
	 */
	public function getExample()
	{
		return $this->example;
	}

	/**
	 * @param mixed $example
	 */
	public function setExample($example): void
	{
		$this->example = $example;
	}

	/**
	 * @param array $groups
	 * @return bool
	 */
	public function isRequired(array $groups = []): bool
	{
		if (is_bool($this->required)) {
			return $this->required;
		}
		foreach ($groups as $group) {
			if (array_search($group, $this->required) !== NULL) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * @param bool|array $required
	 */
	public function setRequired($required): void
	{
		$this->required = $required;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
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
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 * @return string|null
	 */
	public function getType(): ?string
	{
		return $this->type;
	}

	/**
	 * @param string|null $type
	 */
	public function setType(?string $type)
	{
		$this->type = $type;
	}

	/**
	 * @param string $class
	 * @param string $property
	 */
	public function setDeclare(string $class, string $property)
	{
		$this->declareClass = $class;
		$this->declareProperty = $property;
	}

	/**
	 * @return string|null
	 */
	public function getDeclareClass(): ?string
	{
		return $this->declareClass;
	}

	/**
	 * @return string|null
	 */
	public function getDeclareProperty(): ?string
	{
		return $this->declareProperty;
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
	 * @param array $list
	 * @return bool
	 */
	public function isAllowGroups(array $list): bool
	{
		foreach ($list as $group) {
			if (array_search($group, $this->getGroups()) !== FALSE) {
				return TRUE;
			}
		}
		if (empty($this->getGroups())) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @return string|null
	 */
	public function getTypeClass(): ?string
	{
		return $this->typeClass;
	}

	/**
	 * @param string|null $typeClass
	 */
	public function setTypeClass(?string $typeClass): void
	{
		$this->typeClass = $typeClass;
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	public function hasType(string $type)
	{
		return $this->type === $type;
	}

}
