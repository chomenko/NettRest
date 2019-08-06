<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest;

use Chomenko\InlineRouting\IAnnotationExtension;
use Chomenko\NettRest\API\BaseAnnotation;
use Doctrine\Common\Annotations\Annotation;
use Nette\Http\Request;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class API extends BaseAnnotation implements IAnnotationExtension
{

	/**
	 * @var string
	 */
	private $description;

	/**
	 * @var string|null
	 */
	private $section;

	/**
	 * @var string|null
	 */
	private $label;

	/**
	 * @return string
	 */
	public function getExtensionService(): string
	{
		return Provider::class;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 */
	public function setDescription(string $description): void
	{
		$this->description = $description;
	}

	/**
	 * @return string|null
	 */
	public function getSection(): ?string
	{
		return $this->section;
	}

	/**
	 * @param string|null $section
	 */
	public function setSection(?string $section): void
	{
		$this->section = $section;
	}

	/**
	 * @return string|null
	 */
	public function getLabel(): ?string
	{
		return $this->label;
	}

	/**
	 * @param string|null $label
	 */
	public function setLabel(?string $label): void
	{
		$this->label = $label;
	}

}
