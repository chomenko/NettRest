<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Structure;

interface IStructure
{

	/**
	 * @param string $name
	 * @param IStructure $child
	 */
	public function addField(string $name, IStructure $child): void;

	/**
	 * @return IStructure[]
	 */
	public function getFields(): array;

	/**
	 * @param string $name
	 * @return IStructure|null
	 */
	public function getField(string $name): ?IStructure;

	/**
	 * @return IStructure
	 */
	public function getParent();

}
