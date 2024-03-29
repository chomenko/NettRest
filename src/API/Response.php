<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\API;

use Chomenko\NettRest\Metadata\Parameter;
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
	 * @var Parameter[]
	 */
	private $parameters = [];

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
	 * @return \Chomenko\NettRest\API\Parameter[]
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
				throw new \InvalidArgumentException("Parameter must instanceof " . IParameter::class);
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

}
