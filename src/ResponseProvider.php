<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest;

use Chomenko\InlineRouting\Events;
use Chomenko\InlineRouting\Route;
use Chomenko\NettRest\Metadata\Metadata;
use Chomenko\NettRest\Metadata\Method;
use Kdyby\Events\Subscriber;
use Nette\Application\UI\Presenter;

class ResponseProvider implements Subscriber
{

	/**
	 * @var Metadata
	 */
	private $metadata;

	/**
	 * @param Metadata $metadata
	 */
	public function __construct(Metadata $metadata)
	{
		$this->metadata = $metadata;
	}

	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return [
			Events::INVOKED_METHOD => "onInvokedMethod",
		];
	}

	public function onInvokedMethod(Presenter $presenter, Route $route, $result)
	{
		$method = $route->getOption(RequestProvider::ROUTE_METHOD_KEY);
		if (!$method instanceof Method) {
			return;
		}
		dump($method);
		exit;
	}

}
