<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest;

use Nette\Application\IRouter;
use Nette\Application\Request as AppRequest;
use Nette\Http\IRequest;
use Nette\Http\Url;

class ApiRouter implements IRouter
{

	public function match(IRequest $httpRequest)
	{
		$path = $httpRequest->getUrl()->getPath();
		$path = trim($path, "/");
		$list = explode("/", $path);

		if (!isset($list[0]) || $list[0] !== "api") {
			return NULL;
		}
		$name = ["Api"];
		$version = "v1";
		if (isset($list[1])) {
			$version = $list[1];
		}
		$name[] = $version;

		$presenter = "Doc";
		if (isset($list[2])) {
			$presenter = $this->actionName($list[2]);
		}
		$name[] = $presenter;

		foreach ($list as $i => $item) {
			if ($i > 2) {
				$name[] = lcfirst($this->actionName($item));
			}
		}

		return new AppRequest(implode(":", $name), "POST", [
			'action' => 'default',
			'version' => $version,
		]);
	}

	/**
	 * @param string $name
	 * @return string
	 */
	private function actionName($name)
	{
		$exp = explode("-", $name);
		$exp = array_map('ucfirst', $exp);
		return implode("", $exp);
	}

	public function constructUrl(AppRequest $appRequest, Url $refUrl)
	{
		return NULL;
	}

}
