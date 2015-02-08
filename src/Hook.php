<?php

namespace Lexinek\GitAutodeployHook;

use Tracy\Debugger,
	Nette\Http;

/**
 *  @author Jan Mikes <j.mikes@me.com>
 *  @copyright Jan Mikes - janmikes.cz
 */
class Hook
{
	public static $logPriority = "git-hook";

	/** @var [] */
	private $allowedIpAddresses;

	/** @var [] */
	private $allowedMethods;

	/** @var [] */
	private $allowedUserAgents;

	/** @var Nette\Http\Request */
	private $httpRequest;


	public function __construct(
		array $allowedIpAddresses = array(),
		array $allowedMethods = array(),
		array $allowedUserAgents = array(),
		$logDirectory = NULL
	) {
		$httpRequestFactory = new Http\RequestFactory;

		$this->allowedIpAddresses = $allowedIpAddresses;
		$this->allowedMethods = $allowedMethods;
		$this->allowedUserAgents = $allowedUserAgents;
		$this->httpRequest = $httpRequestFactory->createHttpRequest();

		if (!Debugger::isEnabled() && $logDirectory) {
			Debugger::enable(Debugger::DETECT, $logDirectory);
			Debugger::$logSeverity = E_NOTICE | E_WARNING;
		}
	}


	public function isValidMethod()
	{
		if ($this->allowedMethods && !in_array($this->httpRequest->getMethod(), $this->allowedMethods)) {
			$this->log("Invalid method: " . $this->httpRequest->getMethod());
			return false;
		}
		return true;
	}


	public function isValidIp()
	{
		if ($this->allowedIpAddresses && !in_array($this->httpRequest->getRemoteAddress(), $this->allowedIpAddresses)) {
			$this->log("Invalid IP address: " . $this->httpRequest->getRemoteAddress());
			return false;
		}
		return true;
	}


	public function isValidUserAgent()
	{
		if ($this->allowedUserAgents && !in_array($this->httpRequest->getHeader("user-agent"), $this->allowedUserAgents)) {
			$this->log("Invalid user-agent: " . $this->httpRequest->getHeader("user-agent"));
			return false;
		}
		return true;
	}


	public function execPull()
	{
		if ($this->isRequestValid()) {
			$ret = shell_exec("sudo git fetch 2>&1") . " | ";
			$ret .= shell_exec("sudo git pull 2>&1");

			$this->log($ret);
		}
	}


	public function execFunc(callable $function)
	{
		if ($this->isRequestValid()) {
			if (is_callable($function)) {
				ob_start();
				call_user_func($function);
				$this->log(ob_get_contents());
				ob_end_clean();
			}
		}
	}


	public function isRequestValid()
	{
		return ($this->isValidMethod() && $this->isValidIp() && $this->isValidUserAgent());
	}


	public function log($msg)
	{
		if (Debugger::isEnabled()) {
			Debugger::log($msg, self::$logPriority);
		}
	}
}