<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\API;

use Doctrine\Common\Annotations\Annotation;
use InvalidArgumentException;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Request extends BaseAnnotation
{

	/**
	 * @var string|null
	 */
	private $description;

	/**
	 * @var Parameter[]
	 */
	private $parameters = [];

	/**
	 * @var string|null
	 */
	private $into;

	/**
	 * @var string|null
	 */
	private $class;

	/**
	 * @var bool
	 */
	private $collection = FALSE;

	/**
	 * @var array
	 */
	private $groups = [];

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
	 * @return Parameter[]
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	/**
	 * @param Parameter[] $parameters
	 */
	public function setParameters(array $parameters): void
	{
		foreach ($parameters as $parameter) {
			if (!$parameter instanceof IParameter) {
				throw new InvalidArgumentException("Parameter must instanceof " . IParameter::class);
			}
		}
		$this->parameters = $parameters;
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
	 * @return string|null
	 */
	public function getInto(): ?string
	{
		return $this->into;
	}

	/**
	 * @param string|null $into
	 */
	public function setInto(?string $into): void
	{
		$this->into = $into;
	}

}
