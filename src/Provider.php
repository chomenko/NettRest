<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest;

use Chomenko\InlineRouting\Arguments;
use Chomenko\InlineRouting\Extension;
use Chomenko\InlineRouting\IAnnotationExtension;
use Chomenko\InlineRouting\Route;
use Chomenko\NettRest\Exceptions\ApiException;
use Chomenko\InlineRouting\Exceptions\BadRequestException;
use Chomenko\NettRest\Metadata\Metadata;
use Chomenko\NettRest\Metadata\Method;
use Doctrine\Common\Collections\Collection;
use Kdyby\Events\Subscriber;
use Nette\Application\AbortException;
use Nette\Application\Application;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;
use Tracy\ILogger;

class Provider extends Extension implements Subscriber
{

	const ROUTE_METHOD_KEY = "_api_method";

	/**
	 * @var Metadata
	 */
	private $metadata;

	/**
	 * @var RequestBuilder
	 */
	private $requestBuilder;

	/**
	 * @var IResponse
	 */
	private $httpResponse;

	/**
	 * @var Response
	 */
	private $response;

	/**
	 * @var ILogger
	 */
	private $logger;

	/**
	 * @param Metadata $metadata
	 * @param RequestBuilder $requestBuilder
	 * @param IResponse $httpResponse
	 * @param Response $response
	 * @param ILogger $logger
	 */
	public function __construct(
		Metadata $metadata,
		RequestBuilder $requestBuilder,
		IResponse $httpResponse,
		Response $response,
		ILogger $logger
	) {
		$this->metadata = $metadata;
		$this->requestBuilder = $requestBuilder;
		$this->httpResponse = $httpResponse;
		$this->response = $response;
		$this->logger = $logger;
	}

	/**
	 * @return array|string[]
	 */
	public function getSubscribedEvents()
	{
		return [
			Application::class . "::onResponse" => "onResponse",
			Application::class . "::onError" => "onError",
		];
	}

	/**
	 * @param Application $application
	 * @param \Exception|\Error $exception
	 * @throws \Nette\Application\ApplicationException
	 * @throws \Nette\Application\BadRequestException
	 * @throws \Nette\Application\InvalidPresenterException
	 */
	public function onError(Application $application, $exception)
	{
		$requests = $application->getRequests();
		/** @var Presenter $presenter */
		$presenter = $application->getPresenter();
		$request = end($requests);
		$route = $request->getParameter("_route");
		if (!$route instanceof Route) {
			return;
		}

		$apiMethod = $route->getOption(self::ROUTE_METHOD_KEY);
		if (!$apiMethod instanceof Method) {
			return;
		}

		if ($exception instanceof ApiException) {
			$this->response->addError(
				$exception->getMessage(),
				$exception->getName(),
				$exception->getType()
			);
		} elseif ($exception instanceof BadRequestException) {
			$attribute = $exception->getParameter();
			if ($attribute) {
				$this->response->addError(
					$exception->getMessage(),
					$attribute->getName(),
					Response::ERROR_TYPE_PARAMETER
				);
			} else {
				$this->response->addError(
					$exception->getMessage(),
					NULL,
					Response::ERROR_TYPE_REQUEST
				);
			}
		} else {
			$file = $this->logger->log($exception, ILogger::EXCEPTION);
			$name = basename($file, ".html");
			$this->response->addError(
				"Server error. Report this error to the administrator. Error log '{$name}'",
				NULL,
				Response::ERROR_TYPE_SERVER
			);
		}

		$code = $exception->getCode();
		if (!$code) {
			$code = 500;
		}
		$this->httpResponse->setCode($code);
		$this->response->setCode($code);

		try {
			$presenter->forward("api-error-response");
		} catch (AbortException $foo) {
			$application->processRequest($presenter->getLastCreatedRequest());
		}
		exit;
	}

	/**
	 * @param Application $application
	 * @param \Nette\Application\IResponse $response
	 */
	public function onResponse(Application $application, \Nette\Application\IResponse $response)
	{
		if ($response instanceof JsonResponse) {
			$payload = $response->getPayload();
			if ($payload instanceof Response) {

				$code = $this->httpResponse->getCode();
				if ($code !== 200) {
					$payload->setCode($this->httpResponse->getCode());
				} else {
					$errors = $payload->getErrors();
					if ($errors) {
						$error = $errors[0];
						$type = $error["type"];
						$code = 500;
						if ($type == Response::ERROR_TYPE_PARAMETER || $type == Response::ERROR_TYPE_ATTRIBUTE) {
							$code = 422;
						}
						$this->httpResponse->setCode($code);
						$payload->setCode($code);
					}
				}
			}
		}
	}

	/**
	 * @param Presenter $presenter
	 * @param Route $route
	 * @param IAnnotationExtension $annotation
	 * @param array $parameters
	 * @param Arguments $arguments
	 * @param \ReflectionMethod $method
	 * @return mixed|void
	 * @throws \Nette\Application\BadRequestException
	 * @throws AbortException
	 * @throws ApiException
	 */
	public function invoke(Presenter $presenter, Route $route, IAnnotationExtension $annotation, array $parameters, Arguments $arguments, \ReflectionMethod $method): void
	{
		$apiMethod = $route->getOption(self::ROUTE_METHOD_KEY);
		if (!$apiMethod instanceof Method) {
			return;
		}
		$this->requestBuilder->processRawData($apiMethod, $route, $parameters);
		if (!$apiMethod->isValid()) {
			$presenter->sendJson($this->response);
		}
		$this->requestBuilder->processSetsArguments($apiMethod, $arguments);
	}

	/**
	 * @param Presenter $presenter
	 * @param Route $route
	 * @param mixed $result
	 * @throws \Nette\Application\AbortException
	 */
	public function invoked(Presenter $presenter, Route $route, $result): void
	{
		$apiMethod = $route->getOption(self::ROUTE_METHOD_KEY);
		if (!$apiMethod) {
			return;
		}

		if ($result instanceof Collection) {
			$result = $result->toArray();
		}

//		$payload = new Payload();
//		$response = new JsonResponse($payload);

//		dump((array) $payload);
//		exit;
//		$presenter->sendResponse();

		$presenter->sendJson($this->response);
	}

}
