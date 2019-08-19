<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Components;

interface IRenderControl
{

	/**
	 * @return RenderControl
	 */
	public function create(): RenderControl;

}
