<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Subscribers;

use Chomenko\InlineRouting\Route;
use Chomenko\InlineRouting\Routing;
use Chomenko\NettRest\Exceptions\ApiException;
use Chomenko\InlineRouting\Exceptions\BadRequestException;
use Chomenko\NettRest\Response;
use Chomenko\NettRest\Structure\Structure;
use Kdyby\Events\Subscriber;
use Nette\Application\AbortException;
use Nette\Application\Application as App;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Presenter;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Tracy\ILogger;

class Application implements Subscriber
{

	const ROUTE_METHOD_KEY = "_api_method";

	/**
	 * @var Structure
	 */
	private $structure;

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
	 * @var Routing
	 */
	private $routing;

	/**
	 * @var IRequest
	 */
	private $httpRequest;

	/**
	 * @param Structure $structure
	 * @param IResponse $httpResponse
	 * @param IRequest $httpRequest
	 * @param Response $response
	 * @param ILogger $logger
	 * @param Routing $routing
	 */
	public function __construct(
		Structure $structure,
		IResponse $httpResponse,
		IRequest $httpRequest,
		Response $response,
		ILogger $logger,
		Routing $routing
	) {
		$this->structure = $structure;
		$this->httpResponse = $httpResponse;
		$this->response = $response;
		$this->logger = $logger;
		$this->routing = $routing;
		$this->httpRequest = $httpRequest;
	}

	/**
	 * @return array|string[]
	 */
	public function getSubscribedEvents()
	{
		return [
			App::class . "::onResponse" => "onResponse",
			App::class . "::onError" => "onError",
		];
	}

	/**
	 * @param App $application
	 * @param \Exception|\Error $exception
	 * @throws \Nette\Application\ApplicationException
	 * @throws \Nette\Application\BadRequestException
	 * @throws \Nette\Application\InvalidPresenterException
	 */
	public function onError(App $application, $exception)
	{
		$requests = $application->getRequests();
		/** @var Presenter $presenter */
		$presenter = $application->getPresenter();
		$apiRequest = FALSE;
		$request = end($requests);

		if ($request) {
			$route = $request->getParameter("_route");
			if ($route instanceof Route && $this->structure->getMethod($route->getName())) {
				$apiRequest = TRUE;
			}
		}

		if ($exception instanceof MethodNotAllowedException) {
			$apiRequest = TRUE;
			foreach ($exception->getRoutes() as $name => $method) {
				$route = $this->routing->getRoute($name);
				if (!$route->getOption(self::ROUTE_METHOD_KEY)) {
					$apiRequest = FALSE;
					break;
				}
			}
		}

		if (!$apiRequest) {
			return;
		}

		if ($exception instanceof ApiException) {
			$this->response->addError(
				$exception->getMessage(),
				$exception->getName(),
				$exception->getType()
			);
		} elseif ($exception instanceof MethodNotAllowedException) {
			$methods = implode(", ", $exception->getRoutes());
			$this->response->addError(
				"Method Not Allowed. Use these ({$methods})",
				NULL,
				Response::ERROR_TYPE_REQUEST
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

		if (!$presenter) {
			$response = new JsonResponse($this->response);
			$response->send($this->httpRequest, $this->httpResponse);
			exit;
		}

		try {
			$presenter->forward("api-error-response");
		} catch (AbortException $foo) {
			$application->processRequest($presenter->getLastCreatedRequest());
		}
		exit;
	}

	/**
	 * @param App $application
	 * @param \Nette\Application\IResponse $response
	 */
	public function onResponse(App $application, \Nette\Application\IResponse $response)
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

}
