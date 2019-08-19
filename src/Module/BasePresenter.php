<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Module;

use Chomenko\InlineRouting\InlineRouting;
use Chomenko\NettRest\Config;
use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter implements IApiPresenter
{

	use InlineRouting;

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @param Config $config
	 */
	public function injectConfig(Config $config)
	{
		$this->config = $config;
	}

	public function startup()
	{
		parent::startup();
		$this->template->config = $this->config;
		$this->template->assets = $this->config->getAssets();
	}

}
