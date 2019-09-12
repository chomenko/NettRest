<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Subscribers;

use Chomenko\InlineRouting\Arguments;
use Chomenko\InlineRouting\Events;
use Chomenko\InlineRouting\Extension;
use Chomenko\InlineRouting\IAnnotationExtension;
use Chomenko\InlineRouting\Route;
use Chomenko\NettRest\Exceptions\ApiException;
use Chomenko\NettRest\Response;
use Chomenko\NettRest\ResponseDriver;
use Chomenko\NettRest\Structure\Method;
use Chomenko\NettRest\Structure\Structure;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManager;
use Kdyby\Events\Subscriber;
use Nette\Application\AbortException;
use Nette\Application\UI\Presenter;
use ReflectionMethod;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Routing\RouteCollection;
use Chomenko\InlineRouting\Exceptions\RouteException;
use Tracy\Debugger;

class InlineRouting extends Extension implements Subscriber
{

	/**
	 * @var Structure
	 */
	private $structure;

	/**
	 * @var Response
	 */
	private $response;

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	/**
	 * @var ResponseDriver
	 */
	private $responseBuilder;

	public function __construct(
		EntityManager $entityManager,
		Structure $structure,
		Response $response,
		ResponseDriver $responseBuilder
	) {
		$this->structure = $structure;
		$this->response = $response;
		$this->entityManager = $entityManager;
		$this->responseBuilder = $responseBuilder;
	}

	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return [
			Events::INITIALIZE_ROUTE => "onInitializeRoute",
			Events::INITIALIZED => "onInitialized",
		];
	}

	/**
	 * @param Route $route
	 * @param ReflectionClass $class
	 * @param ReflectionMethod $method
	 */
	public function onInitializeRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method)
	{
		$this->structure->routeInitialize($route, $class, $method);
	}

	/**
	 * @param RouteCollection $collection
	 * @throws ReflectionException
	 * @throws RouteException
	 * @throws \Throwable
	 */
	public function onInitialized(RouteCollection $collection)
	{
		$this->structure->routeInitialized($collection);
	}

	/**
	 * @param Presenter $presenter
	 * @param Route $route
	 * @param IAnnotationExtension $annotation
	 * @param array $parameters
	 * @param Arguments $arguments
	 * @param ReflectionMethod $reflection
	 * @return mixed|void
	 * @throws AbortException
	 * @throws ApiException
	 * @throws ReflectionException
	 * @throws RouteException
	 * @throws \Nette\Application\BadRequestException
	 */
	public function invoke(Presenter $presenter, Route $route, IAnnotationExtension $annotation, array $parameters, Arguments $arguments, \ReflectionMethod $reflection): void
	{
		$method = $this->structure->getMethod($route->getName());
		if (!$method instanceof Method) {
			return;
		}

		Debugger::$showBar = FALSE;

		$request = $method->getRequest();
		$request->execute($presenter->getHttpRequest(), $arguments, $parameters);

		foreach ($request->getErrorFields() as $errorField) {
			foreach ($errorField->getErrors() as $error) {
				$this->response->addError(
					$error,
					$errorField->getFullName(),
					$errorField->isUrlParameter() ? Response::ERROR_TYPE_PARAMETER : Response::ERROR_TYPE_ATTRIBUTE
				);
			}
		}
		if (!$request->isValid()) {
			if (!$this->response->getErrors()) {
				throw new ApiException("Request is not valid.", 400, Response::ERROR_TYPE_REQUEST);
			}
			$presenter->sendJson($this->response);
		}
	}

	/**
	 * @param Presenter $presenter
	 * @param Route $route
	 * @param mixed $result
	 * @throws AbortException
	 * @throws ReflectionException
	 * @throws RouteException
	 */
	public function invoked(Presenter $presenter, Route $route, $result): void
	{
		$method = $this->structure->getMethod($route->getName());
		if (!$method instanceof Method) {
			return;
		}

		$data = $this->responseBuilder->createResponseData($method, $result);
		$this->response->setData($data);
		$presenter->sendJson($this->response);
	}

}
