<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Metadata;

abstract class MetaHierarchy implements IMetaHierarchy
{

	/**
	 * @var string|null
	 */
	private $fullName;

	/**
	 * @var Parameter[]
	 */
	protected $parameters = [];

	/**
	 * @var Parameter|Method
	 */
	protected $parent;

	/**
	 * MetaHierarchy constructor.
	 * @param string $fullName
	 */
	public function __construct(string $fullName)
	{
		$this->fullName = $fullName;
	}

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
	 * @return Parameter[]
	 */
	public function getParamsTypeAttributes(): array
	{
		$list = [];
		foreach ($this->parameters as $parameter) {
			if (!$parameter->isUrlParameter()) {
				$list[] = $parameter;
			}
		}
		return $list;
	}

	/**
	 * @return Parameter[]
	 */
	public function getParamsTypeParameters(): array
	{
		$list = [];
		foreach ($this->parameters as $parameter) {
			if ($parameter->isUrlParameter()) {
				$list[] = $parameter;
			}
		}
		return $list;
	}

	/**
	 * @return IMetaHierarchy|null
	 */
	public function getParent(): ?IMetaHierarchy
	{
		return $this->parent;
	}

	/**
	 * @param IMetaHierarchy|null $parent
	 */
	public function setParent(?IMetaHierarchy $parent): void
	{
		$this->parent = $parent;
	}

	/**
	 * @return string|null
	 */
	public function getFullName(): ?string
	{
		return $this->fullName;
	}

	/**
	 * @return Method
	 */
	public function getMethod(): Method
	{
		if ($this instanceof Method) {
			return $this;
		}
		return $this->parent->getMethod();
	}

}
