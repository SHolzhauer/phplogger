<?php

class Logging{

  private $ini = parse_ini_file("phplogger.ini", true);
  // Determine where to log to
  if ($ini['endpoints']['console'] == 1){
    private $endpoint = 'console';
  }
  elseif ($ini['endpoints']['elasticsearch'] == 1) {
    private $endpoint = 'elasticsearch';
  }
  elseif ($ini['endpoints']['logstash'] == 1) {
    private $endpoint = 'logstash';
  }
  elseif ($ini['endpoints']['aws-firehose'] == 1) {
    private $endpoint = 'aws-firehose';
  }
  elseif ($ini['endpoints']['aws-cloudwatch'] == 1) {
    private $endpoint = 'aws-cloudwatch';
  } else {
    private $endpoint = 'console';
  }

  public function debug($message, $location, $fields = null){
			if(!is_null($message) && !is_null($location) && $_ENV['loglevel'] == "debug"){
					$this->send("DEBUG", $location, $message, null, $fields);
					return true;
			} else {
					return false;
			}
	}

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

	private function send($lvl, $location, $message, $stacktrace = false, $fields = null){
		$fh = new Aws\Firehose\FirehoseClient([
			  'region' => 'eu-west-1',
				'version' => 'latest'
		]);

		// Create timestamp for logging
		$uTimeStamp = microtime(true);
		$dtTimeStamp = floor($uTimeStamp);
		$intMilliseconds = round(($uTimeStamp - $dtTimeStamp) * 1000000);
		$strMilliseconds = str_pad($intMilliseconds, 6, '0', STR_PAD_LEFT);
		$timestamp = date(preg_replace('`(?<!\\\\)u`', $strMilliseconds, 'Y-m-d H:i:s.u T'), $dtTimeStamp);

		// stacktrace if wanted
		if($stacktrace){
			  $trace = debug_backtrace();
		} else {
			  $trace = null;
		}

		$logpoint = $_ENV['logpoint'];
		switch ($logpoint) {
			case 'aws-firehose':
				// Send the record to firehose for ingestion into ES
				try{
						if(is_null($fields)){
							$res = $fh->putRecord([
								'DeliveryStreamName' => $_ENV['logstream'],
								'Record' => [
									'Data' => json_encode([
										'timestamp' => $timestamp,
										'lvl' => $lvl,
										'location' => $location,
										'message' => $message,
										'stacktrace' => $trace
									])
								]
							]);
						} else {
							$res = $fh->putRecord([
								'DeliveryStreamName' => 'slo-prd-' . $_ENV['DBUSER'],
								'Record' => [
									'Data' => json_encode([
										'timestamp' => $timestamp,
										'lvl' => $lvl,
										'location' => $location,
										'message' => $message,
										'stacktrace' => $trace,
										$fields
									])
								]
							]);
						}
				} catch (Exception $e){
						error_log("Failed to deliver logs to Firehose:" . $e->getMessage() . "\n");
				}
				break;

			case 'console':
				if(is_null($fields)){
					error_log(print_r(
							json_encode([
								'timestamp' => $timestamp,
								'lvl' => $lvl,
								'location' => $location,
								'message' => $message,
								'stacktrace' => $trace
							]), True));
				} else {
					error_log(print_r(
							json_encode([
								'timestamp' => $timestamp,
								'lvl' => $lvl,
								'location' => $location,
								'message' => $message,
								'stacktrace' => $trace,
								$fields
							]), True));
				}
				break;

      case 'elasticsearch':
        break;

      case 'logstash':
        break;

      case 'aws-cloudwatch'
        break;

			default:
				if(is_null($fields)){
					error_log(print_r(
							json_encode([
								'timestamp' => $timestamp,
								'lvl' => $lvl,
								'location' => $location,
								'message' => $message,
								'stacktrace' => $trace
							]), True));
				} else {
					error_log(print_r(
							json_encode([
								'timestamp' => $timestamp,
								'lvl' => $lvl,
								'location' => $location,
								'message' => $message,
								'stacktrace' => $trace,
								$fields
							]), True));
				}
				break;
		}
	}
}
?>
