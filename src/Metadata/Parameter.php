<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Metadata;

use Nette\Utils\Html;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class Parameter extends MetaHierarchy
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
	 * @var bool
	 */
	private $required = FALSE;

	/**
	 * @var ClassStructure|null
	 */
	protected $classStructure;

	/**
	 * @var bool
	 */
	protected $urlParameter = FALSE;

	/**
	 * @var string|null
	 */
	protected $type;

	/**
	 * @var string|null
	 */
	protected $class;

	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * @var bool
	 */
	protected $valid = FALSE;

	/**
	 * Parameter constructor.
	 * @param string $name
	 * @param string $fullName
	 * @param string|null $description
	 */
	public function __construct(string $name, string $fullName, ?string $description)
	{
		$this->name = $name;
		$this->description = $description;
		parent::__construct($fullName);
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
	 * @return bool
	 */
	public function isMultiple(): bool
	{
		if (count($this->parameters) > 0) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @return bool
	 */
	public function isRequired(): bool
	{
		return $this->required;
	}

	/**
	 * @param bool $required
	 */
	public function setRequired(bool $required): void
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
	 * @return string|null
	 */
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 * @return ClassStructure|null
	 */
	public function getClassStructure(): ?ClassStructure
	{
		return $this->classStructure;
	}

	/**
	 * @param ClassStructure|null $classStructure
	 */
	public function setClassStructure(?ClassStructure $classStructure): void
	{
		$this->classStructure = $classStructure;
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
	public function setUrlParameter(bool $urlParameter): void
	{
		$this->urlParameter = $urlParameter;
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
	 * @return Html
	 */
	public function render(): Html
	{
		$wrapped = Html::el("li");

		$wrapped->addHtml(Html::el("b", [
			"class" => "name",
		])->setText($this->getName()));

		$type = $this->getType();
		if ($this->isMultiple()) {
			$type = "object";
		}

		$wrapped->addHtml(Html::el("code")
			->setText(" " . $type . ":" . ($this->isRequired() ? "required" : "optional")));

		$wrapped->addHtml(Html::el("span")
			->setText(" " . $this->getDescription()));

		$parameters = $this->getParameters();
		if (count($parameters) > 0) {
			$ul = Html::el("ul", ["class" => "attributes"]);
			foreach ($parameters as $parameter) {
				$ul->addHtml($parameter->render());
			}
			$wrapped->addHtml($ul);
		}

		return $wrapped;
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
	 * @return bool
	 */
	public function isValid(): bool
	{
		return $this->valid;
	}

	/**
	 * @param bool $valid
	 */
	public function setValid(bool $valid): void
	{
		$this->valid = $valid;
	}

}
