<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\View;

use Chomenko\NettRest\Components\IContentControl;
use Chomenko\NettRest\Structure\Method;
use Nette\Utils\Strings;

class Column
{

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $link;

	/**
	 * @var Column[]
	 */
	private $columns = [];

	/**
	 * @var Column|Section
	 */
	private $parent;

	/**
	 * @var string
	 */
	private $component;

	/**
	 * @var Method|null
	 */
	private $apiMethod;

	/**
	 * @var string|null
	 */
	private $label;

	/**
	 * @var string|null
	 */
	private $description;

	/**
	 * Section constructor.
	 * @param Section|Column $parent
	 * @param string $name
	 * @param string|null $label
	 * @param string $component
	 */
	public function __construct($parent, string $name, ?string $label = NULL, string $component = IContentControl::class)
	{
		if (!class_exists($component) && !interface_exists($component)) {
			throw new \InvalidArgumentException("Component class '{$component}' doesn't exists.");
		}
		$this->name = $name;
		if ($label) {
			$this->link = $parent->getLink() . "/" . Strings::webalize($label);
		} else {
			$label = $name;
		}
		$this->parent = $parent;
		$this->component = $component;
		$this->label = $label;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return Column[]
	 */
	public function getColumns(): array
	{
		return $this->columns;
	}

	/**
	 * @param Column $column
	 */
	public function addColumn(Column $column): void
	{
		$this->columns[Strings::webalize($column->getName())] = $column;
	}

	/**
	 * @return string
	 */
	public function getLink(): string
	{
		return $this->link;
	}

	/**
	 * @param string $link
	 */
	public function setLink(string $link): void
	{
		$this->link = $link;
	}

	/**
	 * @return Column|Section
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * @return Column|Section
	 */
	public function getSection(): Section
	{
		if ($this->parent instanceof Column) {
			return $this->parent->getSection();
		}
		return $this->parent;
	}

	/**
	 * @return Method|null
	 */
	public function getApiMethod(): ?Method
	{
		return $this->apiMethod;
	}

	/**
	 * @param Method|null $apiMethod
	 */
	public function setApiMethod(?Method $apiMethod): void
	{
		$this->apiMethod = $apiMethod;
	}

	/**
	 * @return string|null
	 */
	public function getLabel(): ?string
	{
		return $this->label;
	}

	/**
	 * @return string
	 */
	public function getComponent(): string
	{
		return $this->component;
	}

	/**
	 * @return string
	 */
	public function getBreadcrumb(): string
	{
		$breadcrumb = [];
		if ($this->parent instanceof Section) {
			$breadcrumb[] = $this->parent->getName();
		} else {
			$breadcrumb[] = $this->parent->getBreadcrumb();
		}
		$breadcrumb[] = $this->getLabel();
		return implode(" / ", array_map("ucfirst", $breadcrumb));
	}

	/**
	 * @return string
	 */
	public function getComponentName(): string
	{
		$prefix = "";
		if ($this->parent instanceof Column) {
			$prefix = $this->parent->getComponentName() . "_";
		} elseif ($this->parent instanceof Section) {
			$prefix = Strings::webalize($this->parent->getName()) . "_";
		}

		$name = $prefix . Strings::webalize($this->getName());
		$name = str_replace("-", "_", $name);
		return $name;
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

}
