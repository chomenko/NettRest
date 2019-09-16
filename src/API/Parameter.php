<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\API;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"METHOD", "PROPERTY", "ANNOTATION"})
 */
class Parameter extends BaseAnnotation implements IParameter
{

	const TYPE_MIXED = "mixed";
	const TYPE_STRING = "string";
	const TYPE_ARRAY = "array";
	const TYPE_BOOL = "bool";
	const TYPE_FLOAT = "float";
	const TYPE_DOUBLE = "double";
	const TYPE_INT = "int";
	const TYPE_INTEGER = "integer";
	const TYPE_LONG = "long";
	const TYPE_NULL = "null";
	const TYPE_NUMERIC = "numeric";
	const TYPE_OBJECT = "object";
	const TYPE_REAL = "real";
	const TYPE_SCALAR = "scalar";
	const TYPE_ALNUM = "alnum";
	const TYPE_ALPHA = "alpha";
	const TYPE_CNTRL = "cntrl";
	const TYPE_DIGIT = "digit";
	const TYPE_GRAPH = "graph";
	const TYPE_LOWER = "lower";
	const TYPE_PRINT = "print";
	const TYPE_PUNCT = "punct";
	const TYPE_SPACE = "space";
	const TYPE_UPPER = "upper";
	const TYPE_XDIGIT = "xdigit";

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var bool|array
	 */
	protected $required = FALSE;

	/**
	 * @var array
	 */
	protected $groups = [];

	/**
	 * @var string|null
	 */
	protected $description;

	/**
	 * @var mixed
	 */
	private $example = NULL;

	/**
	 * @var array
	 */
	private $rules = [];

	/**
	 * @var self
	 */
	private $type = self::TYPE_MIXED;

	/**
	 * @var bool
	 */
	private $nullable = FALSE;

	/**
	 * @var string|null
	 */
	private $setter;

	/**
	 * @var string|null
	 */
	private $getter;

	/**
	 * @var array
	 */
	private $parameters = [];

	/**
	 * @var bool
	 */
	private $collection = FALSE;

	/**
	 * @return array
	 */
	public function getTypes(): array
	{
		return [
			self::TYPE_MIXED => self::TYPE_MIXED,
			self::TYPE_STRING => self::TYPE_STRING,
			self::TYPE_ARRAY => self::TYPE_ARRAY,
			self::TYPE_BOOL => self::TYPE_BOOL,
			self::TYPE_FLOAT => self::TYPE_FLOAT,
			self::TYPE_DOUBLE => self::TYPE_DOUBLE,
			self::TYPE_INT => self::TYPE_INT,
			self::TYPE_INTEGER => self::TYPE_INTEGER,
			self::TYPE_LONG => self::TYPE_LONG,
			self::TYPE_NULL => self::TYPE_NULL,
			self::TYPE_NUMERIC => self::TYPE_NUMERIC,
			self::TYPE_OBJECT => self::TYPE_OBJECT,
			self::TYPE_REAL => self::TYPE_REAL,
			self::TYPE_SCALAR => self::TYPE_SCALAR,
			self::TYPE_ALNUM => self::TYPE_ALNUM,
			self::TYPE_ALPHA => self::TYPE_ALPHA,
			self::TYPE_CNTRL => self::TYPE_CNTRL,
			self::TYPE_DIGIT => self::TYPE_DIGIT,
			self::TYPE_GRAPH => self::TYPE_GRAPH,
			self::TYPE_LOWER => self::TYPE_LOWER,
			self::TYPE_PRINT => self::TYPE_PRINT,
			self::TYPE_PUNCT => self::TYPE_PUNCT,
			self::TYPE_SPACE => self::TYPE_SPACE,
			self::TYPE_UPPER => self::TYPE_UPPER,
			self::TYPE_XDIGIT => self::TYPE_XDIGIT,
		];
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
	 * @return array
	 */
	public function getRules(): array
	{
		return $this->rules;
	}

	/**
	 * @param array $rules
	 */
	public function setRules(array $rules): void
	{
		$this->rules = $rules;
	}

	/**
	 * @return array
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	/**
	 * @param array $parameters
	 */
	public function setParameters(array $parameters): void
	{
		$this->parameters = $parameters;
	}

	/**
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name): void
	{
		$this->name = $name;
	}

	/**
	 * @return bool|array
	 */
	public function getRequired()
	{
		return $this->required;
	}

	/**
	 * @param bool|array $required
	 */
	public function setRequired($required): void
	{
		$this->required = $required;
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
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 */
	public function setType(string $type): void
	{
		if (!array_key_exists($type, $this->getTypes()) && !class_exists($type)) {
			throw new \BadMethodCallException(sprintf('Unsupported type "%s" or or no class was found on annotation "%s".', $type, get_class($this)));
		}
		$this->type = $type;
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
	 * @return bool
	 */
	public function isNullable(): bool
	{
		return $this->nullable;
	}

	/**
	 * @param bool $nullable
	 */
	public function setNullable(bool $nullable): void
	{
		$this->nullable = $nullable;
	}

	/**
	 * @return string|null
	 */
	public function getSetter(): ?string
	{
		return $this->setter;
	}

	/**
	 * @param string $setter
	 */
	public function setSetter(string $setter): void
	{
		$this->setter = $setter;
	}

	/**
	 * @return string|null
	 */
	public function getGetter(): ?string
	{
		return $this->getter;
	}

	/**
	 * @param string|null $getter
	 */
	public function setGetter(?string $getter): void
	{
		$this->getter = $getter;
	}

}
