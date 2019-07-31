<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest;

use Chomenko\InlineRouting\Events;
use Chomenko\NettRest\API\IAnnotation;
use Chomenko\NettRest\Metadata\Metadata;
use Doctrine\Common\Annotations\Reader;
use Kdyby\Events\Subscriber;
use Symfony\Component\Routing\Route;

class RoutingProvider implements Subscriber
{

	/**
	 * @var Reader
	 */
	private $reader;

	/**
	 * @var Metadata
	 */
	private $metadata;

	public function __construct(Reader $reader, Metadata $metadata)
	{
		$this->reader = $reader;
		$this->metadata = $metadata;
	}

	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return [
			Events::INITIALIZE_ROUTE => "onInitializeRoute",
		];
	}

	/**
	 * @param Route $route
	 * @param \ReflectionClass $class
	 * @param \ReflectionMethod $method
	 * @param object $annot
	 * @throws \ReflectionException
	 */
	public function onInitializeRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, $annot)
	{
		/** @var API|null $apiMethod */
		$apiMethod = NULL;
		$annotations = [];
		foreach ($this->reader->getMethodAnnotations($method) as $annotation) {
			if ($annotation instanceof API) {
				$apiMethod = $this->metadata->createMethod($route, $method, $annotation);
				continue;
			}
			if ($annotation instanceof IAnnotation) {
				$annotations[] = $annotation;
			}
		}

		if (!$apiMethod) {
			return;
		}

		foreach ($annotations as $annotation) {
			if ($annotation instanceof API\IParameter) {
				$parameter = $this->metadata->createParameter($apiMethod, $annotation, $method);
				$apiMethod->addParameter($parameter);
				continue;
			}

			if ($annotation instanceof API\Response) {
				$this->metadata->createResponse($apiMethod, $method, $annotation);
				continue;
			}
		}
		$compile = $route->compile();

		foreach ($compile->getVariables() as $variable) {
			if (!$apiMethod->getParameter($variable)) {
				$annotation = new API\Parameter([]);
				$annotation->setName($variable);
				if ($value = $route->getDefault($variable)) {
					$annotation->setExample($value);
				}
				$parameter = $this->metadata->createParameter($apiMethod, $annotation, $method);
				$apiMethod->addParameter($parameter);
			}
		}
	}

}
