<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\View;

class Layout
{

	/**
	 * @var Section[]
	 */
	private $sections = [];

	/**
	 * @return Section[]
	 */
	public function getSections(): array
	{
		return $this->sections;
	}

	/**
	 * @param string $name
	 * @return Section|null
	 */
	public function getSection(string $name): ?Section
	{
		return isset($this->sections[$name]) ? $this->sections[$name] : NULL;
	}

	/**
	 * @param Section $section
	 */
	public function addSection(Section $section): void
	{
		$this->sections[$section->getName()] = $section;
	}

}
