<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Metadata;

interface IMetaHierarchy extends IParameters
{

	/**
	 * @return IMetaHierarchy
	 */
	public function getParent(): ?IMetaHierarchy;

	/**
	 * @param IMetaHierarchy|null $parent
	 */
	public function setParent(?IMetaHierarchy $parent): void;

	/**
	 * @return Method
	 */
	public function getMethod(): Method;

}

