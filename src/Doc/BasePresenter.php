<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Doc;

use Chomenko\InlineRouting\InlineRouting;
use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{

	use InlineRouting;

	public function startup()
	{
		parent::startup();
		$this->template->assets = [
			"js" => [
				__DIR__ . "/assets/libs/jquery-3.2.1/jquery.min.js",
				__DIR__ . "/assets/libs/bootstrap-4/bootstrap.min.js",
				__DIR__ . "/assets/libs/ace/ace.js",
				__DIR__ . "/assets/libs/ace/mode/mode-json.js",
				__DIR__ . "/assets/libs/ace/theme/theme-monokai.js",
				__DIR__ . "/assets/script.js",
			],
			"css" => [
				__DIR__ . "/assets/libs/bootstrap-4/bootstrap.min.css",
				__DIR__ . "/assets/style.css",
			],
			"logo" => __DIR__ . "/assets/nettrest_logo.png",
		];
	}

}
