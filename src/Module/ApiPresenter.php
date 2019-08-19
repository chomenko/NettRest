<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Module;

use Nette\Application\UI\Presenter;
use Chomenko\InlineRouting\InlineRouting;

abstract class ApiPresenter extends Presenter implements IApiPresenter
{

	use InlineRouting;

}
