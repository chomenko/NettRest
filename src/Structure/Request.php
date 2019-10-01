<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Structure;

use Chomenko\InlineRouting\Arguments;
use Chomenko\NettRest\Exceptions\ApiException;
use Chomenko\NettRest\Response;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Application\BadRequestException;
use Nette\Utils\Json;
use Nette\Http;
use Nette\Utils\JsonException;

class Request extends FieldsStructure
{

	/**
	 * @var string|null
	 */
	private $into;

	/**
	 * @var array
	 */
	private $variables = [];

	/**
	 * @param Http\Request $request
	 * @param Arguments $arguments
	 * @param array $parameters
	 * @throws ApiException
	 * @throws BadRequestException
	 * @throws \ReflectionException
	 */
	public function execute(Http\Request $request, Arguments $arguments, array $parameters)
	{
		$body = $this->getRawBody($request);
		$this->applyRaws($this->getFields(), $parameters, $body);
		$this->applyArguments($arguments);
	}

	/**
	 * @param Http\Request $request
	 * @return array|mixed
	 * @throws ApiException
	 */
	protected function getRawBody(Http\Request $request)
	{
		$body = $request->getRawBody();
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
		return $body;
	}

	/**
	 * @param Field[] $structure
	 * @param array $urlParams
	 * @param array $body
	 * @throws BadRequestException
	 */
	protected function applyRaws(array $structure, array $urlParams, array $body): void
	{
		foreach ($structure as $field) {
			$value = NULL;
			$used = FALSE;
			$name = $field->getName();
			$parameter = $field->getParameter();

			if (!$field->isUrlParameter() && array_key_exists($name, $body)) {
				$value = $body[$name];
				$used = TRUE;
			}

			if ($field->isUrlParameter() && array_key_exists($name, $urlParams)) {
				$value = $urlParams[$name];
				$used = TRUE;
			}

			$value = $this->decodeValue($value);
			$field->setValue($value);
			$field->setUsedInRequest($used);

			if ($parameter->isCollection()) {
				if (is_array($value)) {
					$first = TRUE;
					foreach ($value as $data) {
						if (!$first) {
							$cloneField = clone $field;
							$this->applyRaws($cloneField->getFields(), $urlParams, is_array($data) ? $data : [], $parameter);
							$field->addCollectionItem($cloneField);
						} else {
							$this->applyRaws($field->getFields(), $urlParams, is_array($data) ? $data : [], $parameter);
							$first = FALSE;
						}
					}
				}
			} else {
				if ($field->getFields() && $used) {
					$this->applyRaws($field->getFields(), $urlParams, is_array($value) ? $value : [], $parameter);
				}
			}

			$field->validate();
		}
	}

	/**
	 * @param Arguments $arguments
	 * @throws \ReflectionException
	 */
	protected function applyArguments(Arguments $arguments)
	{
		$into = NULL;
		if ($this->getInto()) {
			$into = $arguments->get($this->getInto());
		}

		foreach ($this->fields as $name => $field) {
			if (!$field->isUsedInRequest()) {
				continue;
			}
			if ($into && !$field->isUrlParameter()) {
				$value = $this->createArgument($field, $into);
				$arguments->set($this->getInto(), $value);
			} else {
				$default = $arguments->get($name);
				$value = $this->createArgument($field, $default);
				$arguments->set($name, $value);
			}
		}
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	private function decodeValue($value)
	{
		return json_decode(
			json_encode($value, JSON_NUMERIC_CHECK | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_SLASHES),
			TRUE
		);
	}

	/**
	 * @param Field $field
	 * @param mixed $default
	 * @param bool $ignoreCollection
	 * @return mixed
	 * @throws \ReflectionException
	 */
	protected function createArgument(Field $field, $default = NULL, $ignoreCollection = FALSE)
	{
		$parameter = $field->getParameter();

		if ($parameter->getType() === "object" && ((!$parameter->isCollection() && !$ignoreCollection)) || ($parameter->isCollection() && $ignoreCollection)) {
			$class = $parameter->getTypeClass();
			$reflection = new \ReflectionClass($class);
			if ($default instanceof $class) {
				$object = $default;
			} else {
				if ($this->isRequiredArgsMethod($reflection, "__constructor")) {
					$object = $reflection->newInstanceWithoutConstructor();
				} else {
					$object = $reflection->newInstance();
				}
			}
			$this->setFieldsInObject($reflection, $field->getFields(), $object);
			return $object;
		} elseif ($parameter->isCollection()) {
			$collection = new ArrayCollection();
			$fields = [$field];
			if ($field->getCollection()) {
				array_push($fields, ...$field->getCollection());
			}
			foreach ($fields as $param) {
				$data = $this->createArgument($param, NULL, TRUE);
				$collection->add($data);
			}
			return $collection;
		} elseif ($field->getParent() instanceof Request && $field->getParent()->getInto()) {
			if (is_object($default)) {
				if ($field->isUrlParameter()) {
					return $default;
				}
				$reflection = new \ReflectionClass(get_class($default));
				$this->setFieldsInObject($reflection, [$field], $default);
				return $default;
			} elseif (is_array($default)) {
				return $default;
			}
		}

		if (is_object($default)) {
			return $default;
		}
		return $field->getValue();
	}

	/**
	 * @param \ReflectionClass $reflection
	 * @param Field[] $fields
	 * @param object $object
	 * @throws \ReflectionException
	 */
	private function setFieldsInObject(\ReflectionClass $reflection, array $fields, object $object)
	{
		foreach ($fields as $field) {
			if (!$field->isUsedInRequest()) {
				continue;
			}
			$name = $field->getName();
			if ($field->getParameter()->getDeclareProperty()) {
				$name = $field->getParameter()->getDeclareProperty();
			}
			$value = $this->createArgument($field);

			$setter = $field->getParameter()->getSetter();
			if ($setter) {
				$method = $reflection->getMethod($setter);
				$value = $method->invokeArgs($object, [$value, $field]);
			}

			$property = $reflection->getProperty($name);
			$property->setAccessible(TRUE);
			$property->setValue($object, $value);
			$property->setAccessible(FALSE);
		}
	}

	/**
	 * @param \ReflectionClass $ref
	 * @param string $name
	 * @return bool
	 * @throws \ReflectionException
	 */
	private function isRequiredArgsMethod(\ReflectionClass $ref, string $name): bool
	{
		if (!$ref->hasMethod($name)) {
			return FALSE;
		}
		$method = $ref->getMethod($name);
		foreach ($method->getParameters() as $parameter) {
			if (!$parameter->isOptional()) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * @param Field[]|null $fields
	 * @return bool
	 */
	public function isValid(array $fields = NULL): bool
	{
		if ($fields === NULL) {
			$fields = $this->fields;
		}
		foreach ($fields as $parameter) {
			if ($parameter->getParameter()->isCollection()) {
				if (!$this->isValid($parameter->getCollection())) {
					return FALSE;
				}
			}
			if (!$parameter->isValid()) {
				return FALSE;
			}
			if (!$this->isValid($parameter->getFields())) {
				return FALSE;
			}
		}
		return TRUE;
	}

	/**
	 * @param Field[] $errors
	 * @param array|NULL $fields
	 * @return Field[]
	 */
	public function getErrorFields(array &$errors = [], array $fields = NULL): array
	{
		if ($fields === NULL) {
			$fields = $this->getFields();
		}
		foreach ($fields as $field) {
			if ($field instanceof Field && !$field->isValid()) {
				$errors[] = $field;
			}
			$this->getErrorFields($errors, $field->getFields());
		}
		return $errors;
	}

	/**
	 * @return string|null
	 */
	public function getInto(): ?string
	{
		return $this->into;
	}

	/**
	 * @param string|null $into
	 */
	public function setInto(?string $into): void
	{
		$this->into = $into;
	}

	/**
	 * @return array
	 */
	public function getVariables(): array
	{
		return $this->variables;
	}

	/**
	 * @param array $variables
	 */
	public function setVariables(array $variables): void
	{
		$this->variables = $variables;
	}

}
