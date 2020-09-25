<?php
namespace Sy\Debug;

use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Sy\Template\Template;

class Logger extends AbstractLogger {

	private $level = array(
		\Psr\Log\LogLevel::EMERGENCY => Log::EMERG,
		\Psr\Log\LogLevel::ALERT     => Log::ALERT,
		\Psr\Log\LogLevel::CRITICAL  => Log::CRIT,
		\Psr\Log\LogLevel::ERROR     => Log::ERR,
		\Psr\Log\LogLevel::WARNING   => Log::WARN,
		\Psr\Log\LogLevel::NOTICE    => Log::NOTICE,
		\Psr\Log\LogLevel::INFO      => Log::INFO,
		\Psr\Log\LogLevel::DEBUG     => Log::DEBUG,
	);

	/**
	 * @var array Array of ILogger
	 */
	private $logHandlers;

	public function __construct() {
		$this->logHandlers = array();
	}

	/**
	 * Get log handlers
	 *
	 * @return array
	 */
	public function getLogHandlers() {
		return $this->logHandlers;
	}

	/**
	 * Add a log handler
	 *
	 * @param ILogger $logHandler
	 * @return void
	 */
	public function addLogHandler(ILogger $logHandler) {
		$this->logHandlers[] = $logHandler;
	}

	/**
	 * Logs with an arbitrary level
	 *
	 * @param mixed   $level
	 * @param string  $message
	 * @param mixed[] $context
	 *
	 * @return void
	 *
	 * @throws \Psr\Log\InvalidArgumentException
	 */
	public function log($level, $message, array $context = array()) {
		if (isset($this->level[$level])) {
			$level = $this->level[$level];
		} elseif (!in_array($level, array_values($this->level))) {
			throw new InvalidArgumentException('Level undefined');
		}
		if (is_object($message)) {
			if (!method_exists($message, '__toString')) {
				throw new InvalidArgumentException('Message object must define the __toString method');
			}
			$message = $message->__toString();
		}
		if (!is_string($message)) {
			throw new InvalidArgumentException('Message must be a string');
		}
		if (!empty($context)) {
			$template = new Template();
			$template->setContent($message);
			foreach ($context as $key => $value) {
				$template->setVar($key, is_object($value) ? $value->__toString() : $value);
			}
			$message = $template->getRender();
		}
		$info = $context;
		$info['message'] = $message;
		$info['level'] = $level;
		$log = new Log($info);

		foreach ($this->logHandlers as $logger) {
			$logger->write($log);
		}
	}

}