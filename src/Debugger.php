<?php
namespace Sy\Debug;

use Sy\Stopwatch;

class Debugger {

	private static $instance;

	/**
	 * @var bool
	 */
	private $phpInfo;

	/**
	 * @var bool
	 */
	private $timeRecord;

	/**
	 * Log handlers pool
	 *
	 * @var array
	 */
	private $loggers;

	/**
	 * @var Logger
	 */
	private $logger;

	/**
	 * @var Stopwatch
	 */
	private $stopwatch;

	private function __construct() {
		$this->phpInfo    = false;
		$this->timeRecord = false;
		$this->loggers    = array();
		$this->logger     = new Logger();
		$this->stopwatch  = new Stopwatch();
	}

	private function __clone() {}

	/**
	 * Singleton method
	 *
	 * @return Debugger
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}

	private function logActive() {
		if (isset($_GET['sy_debug_log']) and $_GET['sy_debug_log'] === 'off') return false;
		return !empty($this->loggers);
	}

	/**
	 * Activate PHP info
	 */
	public function enablePhpInfo() {
		$this->phpInfo = true;
	}

	/**
	 * Activate web logging
	 */
	public function enableWebLog() {
		if ($this->webLogActive()) return;
		$this->loggers['web'] = new WebLogger();
		$this->logger->addLogHandler($this->loggers['web']);
	}

	/**
	 * Activate file logging
	 *
	 * @param string $file log file
	 * @param int $ttl
	 * @param string $dateFormat
	 */
	public function enableFileLog($file, $ttl = 90, $dateFormat = 'Y-m-d H:i:s') {
		if ($this->fileLogActive()) return;
		$this->loggers['file'] = new FileLogger($file, $ttl, $dateFormat);
		$this->logger->addLogHandler($this->loggers['file']);
	}

	/**
	 * Activate tag logging
	 *
	 * @param string $path directory where tagged logs are stored
	 */
	public function enableTagLog($path) {
		if ($this->tagLogActive()) return;
		$this->loggers['tag'] = new TagLogger($path);
		$this->logger->addLogHandler($this->loggers['tag']);
	}

	/**
	 * Activate query logging
	 */
	public function enableQueryLog() {
		if ($this->queryLogActive()) return;
		$this->loggers['query'] = new QueryLogger();
		$this->logger->addLogHandler($this->loggers['query']);
	}

	/**
	 * Activate standard output logging
	 */
	public function enableOutputLog() {
		if ($this->outputLogActive()) return;
		$this->loggers['output'] = new OutputLogger();
		$this->logger->addLogHandler($this->loggers['output']);
	}

	/**
	 * Activate time recording
	 */
	public function enableTimeRecord() {
		$this->timeRecord = true;
	}

	/**
	 * Return if PHP info is activated or not
	 *
	 * @return bool
	 */
	public function phpInfoActive() {
		return $this->phpInfo;
	}

	/**
	 * Return if the Web Logger is activated or not
	 *
	 * @return bool
	 */
	public function webLogActive() {
		return isset($this->loggers['web']);
	}

	/**
	 * Return if the File Logger is activated or not
	 *
	 * @return bool
	 */
	public function fileLogActive() {
		return isset($this->loggers['file']);
	}

	/**
	 * Return if the Query Logger is activated or not
	 *
	 * @return bool
	 */
	public function queryLogActive() {
		return isset($this->loggers['query']);
	}

	/**
	 * Return if the Tag Logger is activated or not
	 *
	 * @return bool
	 */
	public function tagLogActive() {
		return isset($this->loggers['tag']);
	}

	/**
	 * Return if the Output Logger is activated or not
	 *
	 * @return bool
	 */
	public function outputLogActive() {
		return isset($this->loggers['output']);
	}

	/**
	 * Return if the Time Record is activated or not
	 *
	 * @return bool
	 */
	public function timeRecordActive() {
		return $this->timeRecord;
	}

	/**
	 * Return loggers
	 *
	 * @return array
	 */
	public function getLoggers() {
		return $this->loggers;
	}

	/**
	 * Return logger
	 *
	 * @return Logger
	 */
	public function getLogger() {
		return $this->logger;
	}

	/**
	 * Log a message
	 *
	 * @param string|array $message
	 * @param array $info Optionnal associative array. Key available: level, type, file, line, function, class, tag
	 */
	public function log($message, array $info = array()) {
		if (!$this->logActive()) return;
		$message = is_array($message) ? print_r($message, true) : $message;
		$level = isset($info['level']) ? $info['level'] : Log::INFO;
		$this->logger->log($level, $message, $info);
	}

	/**
	 * Log a warning message
	 *
	 * @param string|array $message
	 * @param array $info Optionnal associative array. Key available: type, file, line, function, class, tag
	 */
	public function logWarning($message, array $info = array()) {
		$info['level'] = Log::WARN;
		$this->log($message, $info);
	}

	/**
	 * Log an error message
	 *
	 * @param string|array $message
	 * @param array $info Optionnal associative array. Key available: type, file, line, function, class, tag
	 */
	public function logError($message, array $info = array()) {
		$info['level'] = Log::ERR;
		$this->log($message, $info);
	}

	/**
	 * Log a tagged message. A tagged message will be stored in a tag named file.
	 *
	 * @param string|array $message
	 * @param string $tag
	 * @param array $info Optionnal associative array. Key available: type, file, line, function, class, message, tag
	 */
	public function logTag($message, $tag, array $info = array()) {
		$info['tag'] = $tag;
		$this->log($message, $info);
	}

	/**
	 * Return times array
	 *
	 * @return array
	 */
	public function getTimes() {
		return $this->stopwatch->getTimes();
	}

	/**
	 * Start time record
	 *
	 * @param string $id time record identifier
	 */
	public function timeStart($id) {
		if (!$this->timeRecord) return;
		$this->stopwatch->start($id);
	}

	/**
	 * Stop time record
	 *
	 * @param string $id time record identifier
	 */
	public function timeStop($id) {
		if (!$this->timeRecord) return;
		$this->stopwatch->stop($id);
	}

}