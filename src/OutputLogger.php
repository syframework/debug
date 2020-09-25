<?php
namespace Sy\Debug;

class OutputLogger implements ILogger {

	public function write(Log $log) {
		echo $log->getMessage() . PHP_EOL;
	}

}