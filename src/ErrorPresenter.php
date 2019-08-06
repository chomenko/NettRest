<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest;

use Nette\Application\UI\Presenter;
use Chomenko\InlineRouting\InlineRouting;
use Chomenko\InlineRouting\Inline;

/**
 * @Inline\Route("/api/error/", name="api-error-")
 */
class ErrorPresenter extends Presenter
{
	use InlineRouting;

	/**
	 * @var Response
	 */
	private $response;

	public function __construct(Response $response)
	{
		$this->response = $response;
	}

	/**
	 * @Inline\Route("response", name="response")
	 */
	public function sendErrResponse()
	{
		$this->sendJson($this->response->toArray());
	}

}
