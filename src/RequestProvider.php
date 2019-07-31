<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest;

use Chomenko\InlineRouting\Arguments;
use Chomenko\InlineRouting\Extension;
use Chomenko\InlineRouting\IAnnotationExtension;
use Chomenko\NettRest\Metadata\Metadata;
use Symfony\Component\Routing\Route;

class RequestProvider extends Extension
{

	const ROUTE_METHOD_KEY = "@api_method";

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
	 * @param Route $route
	 * @param IAnnotationExtension $annotation
	 * @param array $parameters
	 * @param Arguments $arguments
	 * @param \ReflectionMethod $method
	 * @return mixed|void
	 */
	public function invoke(Route $route, IAnnotationExtension $annotation, array $parameters, Arguments $arguments, \ReflectionMethod $method)
	{
		$apiMethod = $this->metadata->methodByRoute($route);
		if (!$apiMethod) {
			return;
		}
		$route->setOption(self::ROUTE_METHOD_KEY, $apiMethod);
	}

}
