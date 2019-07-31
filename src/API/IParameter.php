<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\API;

interface IParameter
{

	/**
	 * @return string|null
	 */
	public function getDescription(): ?string;

	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @return array
	 */
	public function getGroups(): array;

	/**
	 * @param array $groups
	 */
	public function setGroups(array $groups): void;

	/**
	 * @return bool
	 */
	public function isRequired(): bool;

	/**
	 * @return mixed
	 */
	public function getExample();

	/**
	 * @return array
	 */
	public function getRules(): array;

	/**
	 * @return array
	 */
	public function getParameters(): array;

	/**
	 * @return string
	 */
	public function getType(): string;

	/**
	 * @param string $type
	 */
	public function setType(string $type): void;

}
