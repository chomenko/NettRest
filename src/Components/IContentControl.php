<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Components;

use Chomenko\NettRest\View\Column;

interface IContentControl
{

	/**
	 * @param Column $column
	 * @return ContentControl
	 */
	public function create(Column $column): ContentControl;

}
