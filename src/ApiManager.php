<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest;

use Chomenko\NettRest\Structure\Structure;

class ApiManager
{

	/**
	 * @var Structure|null
	 */
	private $structure;

	/**
	 * @return Structure|null
	 */
	public function getStructure(): ?Structure
	{
		return $this->structure;
	}

	/**
	 * @param Structure|null $structure
	 */
	public function setStructure(?Structure $structure): void
	{
		$this->structure = $structure;
	}

}
