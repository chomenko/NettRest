<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\API;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Version implements IAnnotation
{

	/**
	 * @var integer|null
	 */
	public $min = NULL;

	/**
	 * @var integer|null
	 */
	public $only = NULL;

	/**
	 * @var integer|null
	 */
	public $max = NULL;

}
