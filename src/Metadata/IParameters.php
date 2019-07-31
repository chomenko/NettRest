<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Metadata;

interface IParameters
{

	/**
	 * @param Parameter $parameter
	 */
	public function addParameter(Parameter $parameter): void;

	/**
	 * @param string $name
	 * @return Parameter|null
	 */
	public function getParameter(string $name): ?Parameter;

	/**
	 * @return Parameter[]
	 */
	public function getParameters(): array;

}
