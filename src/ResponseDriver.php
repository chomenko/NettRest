<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest;

use Chomenko\NettRest\Structure\Field;
use Chomenko\NettRest\Structure\IStructure;
use Chomenko\NettRest\Structure\Method;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Proxy\Proxy;
use InvalidArgumentException;

class ResponseDriver
{

	/**
	 * @var Response
	 */
	private $response;

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	/**
	 * ResponseBuilder constructor.
	 * @param Response $response
	 * @param EntityManager $entityManager
	 */
	public function __construct(Response $response, EntityManager $entityManager)
	{
		$this->response = $response;
		$this->entityManager = $entityManager;
	}

	/**
	 * @param Method $method
	 * @param mixed $values
	 * @return array
	 * @throws \ReflectionException
	 */
	public function createResponseData(Method $method, $values = NULL)
	{
		$response = $method->getResponse();
		if ($response->isCollection()) {
			$values = $this->validateCollection($values);
			$responseData = [];
			foreach ($values as $value) {
				$responseData[] = $this->createResponseFields($response->getFields(), $response->getGroups(), $value);
			}
			return $responseData;
		}
		return $this->createResponseFields($response->getFields(), $response->getGroups(), $values);
	}

	/**
	 * @param Field[] $fields
	 * @param array $groups
	 * @param mixed $values
	 * @return array
	 * @throws \ReflectionException
	 */
	protected function createResponseFields(array $fields, array $groups, $values): array
	{
		$data = [];
		foreach ($fields as $field) {
			if (!$field->getParameter()->isAllowGroups($groups)) {
				continue;
			}
			$responseValue = $this->createResponseField($field, $groups, $values);
			$data[$field->getName()] = $responseValue;
		}
		return $data;
	}


	/**
	 * @param Field $field
	 * @param array|null $groups
	 * @param null $values
	 * @return mixed
	 * @throws \ReflectionException
	 */
	private function createResponseField(Field $field, ?array $groups, $values = NULL)
	{
		$name = $field->getParameter()->getDeclareProperty();
		if (!$name) {
			$name = $field->getName();
		}

		$getter = $field->getParameter()->getGetter();
		if ($getter) {
			$origin = $values;
			$values = $this->getValue($name, $values);
			if (is_object($origin)) {
				$ref = new \ReflectionClass(get_class($origin));
				$method = $ref->getMethod($getter);
				$values = $method->invokeArgs($origin, [$values, $field]);
			}
		} else {
			$values = $this->getValue($name, $values);
		}

		if ($field->getParameter()->isCollection()) {
			$values = $this->validateCollection($values);

			$result = [];
			foreach ($values as $value) {
				$result[] = $this->createResponseFields($field->getFields(), $groups, $value);
			}
			return $result;
		} elseif ($field->getFields()) {
			$values = $this->createResponseFields($field->getFields(), $groups, $values);
		}

		return $values;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	private function validateCollection($value)
	{
		if ($value instanceof Collection) {
			return $value->toArray();
		} elseif (is_array($value)) {
			return $value;
		}
		throw new InvalidArgumentException("Must collection");
	}

	/**
	 * @param string $name
	 * @param mixed $values
	 * @return mixed|null
	 * @throws \ReflectionException
	 */
	private function getValue(string $name, $values)
	{
		if (is_object($values)) {
			return $this->getObjectValue($values, $name);
		} elseif (is_array($values)) {
			return isset($values[$name]) ? $values[$name] : NULL;
		}
		return $values;
	}

	/**
	 * @param object $object
	 * @param string $name
	 * @return mixed
	 * @throws \ReflectionException
	 */
	private function getObjectValue(object $object, string $name)
	{
		if ($this->objectHasEntity($object)) {
			$metadata = $this->entityManager->getClassMetadata(get_class($object));
			if ($object instanceof Proxy && !$object->__isInitialized()) {
				$object->__load();
			}
			return $metadata->getFieldValue($object, $name);
		}

		$ref = new \ReflectionClass(get_class($object));
		$prop = $ref->getProperty($name);
		if (!$prop->isPublic()) {
			$prop->setAccessible(TRUE);
			$value = $prop->getValue($object);
			$prop->setAccessible(FALSE);
			return $value;
		}

		$value = $prop->getValue($object);
		return $value;
	}

	/**
	 * @param object $class
	 * @return bool
	 */
	private function objectHasEntity(object $class): bool
	{
		$class = ($class instanceof Proxy)
			? get_parent_class($class)
			: get_class($class);

		return !$this->entityManager->getMetadataFactory()->isTransient($class);
	}

}
