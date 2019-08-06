<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest;

use Chomenko\InlineRouting\Arguments;
use Chomenko\InlineRouting\Route;
use Chomenko\NettRest\Exceptions\ApiException;
use Chomenko\NettRest\Metadata\Method;
use Chomenko\NettRest\Metadata\Parameter;
use Nette\Application\BadRequestException;
use Nette\Http\Request;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validation;
use Chomenko\NettRest\API\Parameter as AnotParameter;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestBuilder
{

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var ValidatorInterface
	 */
	private $validator;

	/**
	 * @var Response
	 */
	private $response;

	/**
	 * @param Request $request
	 * @param Response $response
	 */
	public function __construct(Request $request, Response $response)
	{
		$this->request = $request;
		$this->response = $response;
		$this->validator = Validation::createValidator();
	}

	/**
	 * @param Method $method
	 * @param Route $route
	 * @param array $parameters
	 * @throws ApiException
	 * @throws BadRequestException
	 */
	public function processRawData(Method $method, Route $route, array $parameters): void
	{
		foreach ($method->getParameters() as $parameter) {
			$parameter->isRequired();
		}
		$body = $this->request->getRawBody();
		if (empty($body)) {
			$body = [];
		} else {
			try {
				$body = Json::decode($body, Json::FORCE_ARRAY);
			} catch (JsonException $exception) {
				throw new ApiException(
					"Unsupported Media Type.",
					415,
					Response::ERROR_TYPE_REQUEST
				);
			}
		}
		$this->buildValues($method->getParameters(), $parameters, $body);
	}

	/**
	 * @param Method $method
	 * @param Arguments $arguments
	 */
	public function processSetsArguments(Method $method, Arguments $arguments): void
	{
	}


	/**
	 * @param Parameter[] $params
	 * @param array $urlParams
	 * @param array $body
	 * @param Parameter|null $parent
	 * @throws ApiException
	 * @throws BadRequestException
	 */
	protected function buildValues(array $params, array $urlParams, array $body, Parameter $parent = NULL): void
	{
		foreach ($params as $param) {
			$value = NULL;
			$isset = FALSE;

			if (!$param->isUrlParameter() && array_key_exists($param->getName(), $body)) {
				$value = $body[$param->getName()];
				$isset = TRUE;
			}

			if ($param->isUrlParameter() && array_key_exists($param->getName(), $urlParams)) {
				$value = $urlParams[$param->getName()];
				$isset = TRUE;
			}

			$value = json_decode(json_encode($value, JSON_NUMERIC_CHECK | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_SLASHES), TRUE);

			$param->setValue($value);

			if ($isset) {
				$this->validateParameter($param);
			}

			if ($param->isRequired() && !$isset) {
				throw new ApiException(
					"Missing " . ($param->isUrlParameter() ? "parameter" : "attribute") . " '{$param->getName()}'" . ($parent ? " in '{$parent->getName()}'." : "."),
					422,
					$param->isUrlParameter() ? Response::ERROR_TYPE_PARAMETER : Response::ERROR_TYPE_ATTRIBUTE,
					$param->getName()
				);
			}

			if ($param->isMultiple() && $isset) {
				$this->buildValues($param->getParameters(), $urlParams, is_array($value) ? $value : [], $param);
			}
		}
	}

	/**
	 * @param Parameter $parameter
	 */
	public function validateParameter(Parameter $parameter)
	{
		$type = $parameter->getType();
		$rules = $parameter->getRules();
		if (!empty($type) && $type !== AnotParameter::TYPE_MIXED && $type !== AnotParameter::TYPE_OBJECT) {
			$constraint = new Type(["type" => $type]);
			$rules[] = $constraint;
		}
		$parameter->setValid(TRUE);
		foreach ($this->validator->validate($parameter->getValue(), $rules) as $violation) {
			if ($violation instanceof ConstraintViolationInterface) {
				$this->response->addError(
					$violation->getMessage(),
					$parameter->getName(),
					$parameter->isUrlParameter() ? Response::ERROR_TYPE_PARAMETER : Response::ERROR_TYPE_ATTRIBUTE
				);
			}
			$parameter->setValid(FALSE);
		}
	}

}
