<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\View;

use Nette\Utils\Strings;

class Section
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
	 * Section constructor.
	 * @param string $name
	 */
	public function __construct(string $name)
	{
		$this->name = $name;
		$this->link = "#" . Strings::webalize($name);
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
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
	 * @return Column[]
	 */
	public function getColumns(): array
	{
		return $this->columns;
	}

	/**
	 * @param string $name
	 * @return Column|null
	 */
	public function getColumn(string $name): ?Column
	{
		return isset($this->columns[$name]) ? $this->columns[$name] : NULL;
	}

	/**
	 * @param Column $column
	 */
	public function addColumn(Column $column): void
	{
		$this->columns[Strings::webalize($column->getName())] = $column;
	}

}
