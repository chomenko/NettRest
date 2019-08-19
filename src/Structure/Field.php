<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Structure;

use Chomenko\NettRest\API;
use InvalidArgumentException;
use Nette\Utils\Html;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Field implements IStructure
{

	/**
	 * @var FieldsStructure|Field
	 */
	protected $parent;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var bool
	 */
	protected $urlParameter = FALSE;

	/**
	 * @var Parameter
	 */
	protected $parameter;

	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * @var bool|NULL
	 */
	protected $valid;

	/**
	 * @var bool
	 */
	protected $usedInRequest = FALSE;

	/**
	 * @var Field[]
	 */
	private $fields = [];

	/**
	 * @var array string
	 */
	private $errors = [];

	/**
	 * @var bool
	 */
	private $recursive = FALSE;

	/**
	 * Structure constructor.
	 * @param FieldsStructure|Field $parent
	 * @param Parameter $parameter
	 */
	public function __construct($parent, Parameter $parameter)
	{
		if (!$parent instanceof FieldsStructure && !$parent instanceof Field) {
			$instances = implode(" or ", [Method::class, self::class]);
			throw new InvalidArgumentException("Parent parameter must by instance {$instances}");
		}
		$this->parent = $parent;
		$this->parameter = $parameter;
		$this->name = $parameter->getName();
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return Parameter
	 */
	public function getParameter(): Parameter
	{
		return $this->parameter;
	}

	/**
	 * @return Method|FieldsStructure|Field
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * @return Method
	 */
	public function getMethod(): Method
	{
		return $this->parent->getMethod();
	}

	/**
	 * @return FieldsStructure
	 */
	public function getFieldStructure(): FieldsStructure
	{
		if ($this->parent instanceof Field) {
			return $this->parent->getFieldStructure();
		}
		return $this->parent;
	}

	/**
	 * @return bool
	 */
	public function isUrlParameter(): bool
	{
		return $this->urlParameter;
	}

	/**
	 * @param bool $urlParameter
	 */
	public function setUrlParameter(bool $urlParameter = TRUE): void
	{
		$this->urlParameter = $urlParameter;
	}

	/**
	 * @param bool $valid
	 */
	public function setValid(bool $valid): void
	{
		$this->valid = $valid;
	}

	/**
	 * @return bool
	 */
	public function isValid(): bool
	{
		return $this->valid || $this->valid === NULL ? TRUE : FALSE;
	}

	/**
	 * @return bool
	 */
	public function isUsedInRequest(): bool
	{
		return $this->usedInRequest;
	}

	/**
	 * @param bool $usedInRequest
	 */
	public function setUsedInRequest(bool $usedInRequest): void
	{
		$this->usedInRequest = $usedInRequest;
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
	 * @return Field[]
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
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param mixed $value
	 */
	public function setValue($value): void
	{
		$this->value = $value;
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
	 * @param ValidatorInterface|NULL $validator
	 */
	public function validate(ValidatorInterface $validator = NULL)
	{
		$this->valid = TRUE;
		if ($this->isRequired() && !$this->isUsedInRequest()) {
			$type = $this->isUrlParameter() ? "parameter" : "attribute";
			$this->addError("Missing {$type} '{$this->getName()}'");
			$this->valid = FALSE;
			return;
		} elseif (!$this->isRequired() && !$this->isUsedInRequest()) {
			return;
		}

		if (!$validator) {
			$validator = Validation::createValidator();
		}

		$parameter = $this->getParameter();
		$type = $parameter->getType();
		$rules = $parameter->getRules();
		if (!empty($type) && $type !== API\Parameter::TYPE_MIXED && $type !== API\Parameter::TYPE_OBJECT) {
			$constraint = new Type(["type" => $type]);
			$rules[] = $constraint;
		}

		foreach ($validator->validate($this->getValue(), $rules) as $violation) {
			if ($violation instanceof ConstraintViolationInterface) {
				$this->errors[] = $violation->getMessage();
			}
			$this->valid = FALSE;
		}
	}

	/**
	 * @return bool
	 */
	public function isRequired(): bool
	{
		$groups = $this->getFieldStructure()->getGroups();
		$parameter = $this->getParameter();
		return $parameter->isRequired($groups);
	}

	/**
	 * @return bool
	 */
	public function isRecursive(): bool
	{
		return $this->recursive;
	}

	/**
	 * @param bool $recursive
	 */
	public function setRecursive(bool $recursive): void
	{
		$this->recursive = $recursive;
	}

}
