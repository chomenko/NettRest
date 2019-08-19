<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Structure;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Proxy\Proxy;
use InvalidArgumentException;

class Response extends FieldsStructure
{

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	/**
	 * @param EntityManager $entityManager
	 * @param mixed $result
	 * @return array
	 * @throws \ReflectionException
	 */
	public function createResponse(EntityManager $entityManager, $result): array
	{
		$this->entityManager = $entityManager;
		$response = [];
		$values = $result;
		if ($this->isCollection()) {
			$values = $this->validateCollection($result);
		}
		if ($this->isCollection()) {
			foreach ($values as $value) {
				$response[] = $this->createResponseFields($this->fields, $this->getGroups(), $value);
			}
			$this->entityManager = NULL;
			return $response;
		}

		$response = $this->createResponseFields($this->fields, $this->getGroups(), $values);
		$this->entityManager = NULL;
		return $response;
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
		$method = $this->getMethod();
		$collectionClass = Collection::class;
		throw new InvalidArgumentException("Method '{$method->getMethodName()}' in '{$method->getMethodName()}' must return 'array' or '{$collectionClass}'");
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
	protected function createResponseField(Field $field, ?array $groups, $values = NULL)
	{
		$name = $field->getParameter()->getDeclareProperty();
		if (!$name) {
			$name = $field->getName();
		}

		if ($this->isCollection()) {
			$values = $this->validateCollection($values);
			return $this->createResponseFields($field->getFields(), $groups, $values);
		}

		return $this->getValue($name, $values);
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
