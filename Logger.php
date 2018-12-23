<?php

class Logging{
  public function info($message, $location, $fields = null){
			if(!is_null($message) && !is_null($location)){
					$this->send("INFO", $location, $message, null, $fields);
					return true;
			} else {
					return false;
			}
	}

	public function warning($message, $location, $fields = null){
			if(!is_null($message) && !is_null($location)){
					$this->send("WARNING", $location, $message, null, $fields);
					return true;
			} else {
					return false;
			}
	}

	public function error($message, $location, $stack = false, $fields = null){
			if(!is_null($message) && !is_null($location)){
					$this->send("ERROR", $location, $message, $stack, $fields);
					return true;
			} else {
					return false;
			}
	}

	public function critical($message, $location, $stack = false, $fields = null){
			if(!is_null($message) && !is_null($location)){
					$this->send("CRITICAL", $location, $message, $stack, $fields);
					return true;
			} else {
					return false;
			}
	}
}
?>
