<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Doc;

use Chomenko\InlineRouting\Inline\Route;
use Chomenko\NettRest\Render;
use Chomenko\NettRest\Metadata\Metadata;

/**
 * @Route("/api", name="api-doc-")
 */
class DocPresenter extends BasePresenter
{

	/**
	 * @var Metadata
	 */
	private $metadata;

	/**
	 * @var Render
	 */
	private $docRender;

	/**
	 * DocPresenter constructor.
	 * @param Metadata $metadata
	 * @param Render $docRender
	 */
	public function __construct(Metadata $metadata, Render $docRender)
	{
		$this->metadata = $metadata;
		$this->docRender = $docRender;
	}

	/**
	 * @Route("/doc", name="default")
	 */
	public function render()
	{
		$this->template->docRender = $this->docRender;
//		$this->template->sections = $this->metadata->getSections();
	}

}
