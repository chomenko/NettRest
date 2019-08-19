<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Module;

use Chomenko\InlineRouting\Inline\Route;
use Chomenko\NettRest\Components\IRenderControl;
use Chomenko\NettRest\Structure\Structure;

/**
 * @Route("/api", name="api-doc-")
 */
class DocPresenter extends BasePresenter
{

	/**
	 * @var Structure
	 */
	private $structure;

	/**
	 * @var IRenderControl
	 */
	private $renderControl;

	/**
	 * @param Structure $structure
	 * @param IRenderControl $renderControl
	 */
	public function __construct(Structure $structure, IRenderControl $renderControl)
	{
		$this->structure = $structure;
		$this->renderControl = $renderControl;
	}

	/**
	 * @Route("/doc", name="default")
	 */
	public function render()
	{
	}

	public function createComponentApiWrapped()
	{
		return $this->renderControl->create();
	}

}
