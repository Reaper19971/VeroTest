<?php
require_once 'Autoloader.php';
Autoloader::register();
new Api();

class Api
{
	private static $db;

	public static function getDb()
	{
		return self::$db;
	}

	public function __construct()
	{
		self::$db = (new Database())->init();

		$uri = strtolower(trim((string)$_SERVER['PATH_INFO'], '/'));
		
		var_dump($uri);
		$httpVerb = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';

		$wildcards = [
			':any' => '[^/]+',
			':num' => '[0-9]+',
		];
		$routes = [
			'get constructionStages' => [
				'class' => 'ConstructionStages',
				'method' => 'getAll',
			],
			'get constructionStages/(:num)' => [
				'class' => 'ConstructionStages',
				'method' => 'getSingle',
			],
			'post constructionStages' => [
				'class' => 'ConstructionStages',
				'method' => 'post',
				'bodyType' => 'ConstructionStagesCreate'
			],
			'patch constructionStages/(:num)' => [ 
				'class' => 'ConstructionStages',
				'method' => 'patchData',
				'bodyType' => 'ConstructionStagesCreate'
			],
			'delete constructionStages/(:num)' => [ 
				'class' => 'ConstructionStages',
				'method' => 'deleteStatement'
			]
		];

		$response = [
			'error' => 'No such route',
		];

		if ($uri){

			foreach ($routes as $pattern => $target) {
				$pattern = str_replace(array_keys($wildcards), array_values($wildcards), $pattern);
				if (preg_match('#^'.$pattern.'$#i', "{$httpVerb} {$uri}", $matches)) {
					$params = [];
					array_shift($matches);
					if ($httpVerb === 'post' && $httpVerb === 'patch') {
						$data = json_decode(file_get_contents('php://input'));
						$params = [new $target['bodyType']($data)];
					}
					$params = array_merge($params, $matches);
					// TASK 2
					$validationSystem($params);
					if($validationSystem[0] == true){
						$response = call_user_func_array([new $target['class'], $target['method']], $validationSystem[1]);	
					}else{
						$response = [
							'error' => 'Validation Check not passed',
						];
					}
					break;
				}
			}

			echo json_encode($response, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		}
	}
	
// TASK 2 Validation Check
	
/**
 * runs a check validation Check for incoming Data
 *
 * @param array     $validationArray       an array of the incoming Data
 *
 * @return array wich contains 2 Variables. First: if the Check has passed. Second: An Array with the new Parameters that has been given (because some may need default Values)
 */
	public static function validationSystem($validationArray){
	
		$validationCheck = true;
		
		// Name Validation
		if(isset($validationArray['name']) && strlen($validationArray['name']) > 255){
			$validationCheck = false;
		}
		
		// Startdate Validation
		if(isset($validationArray['start_date'])){
			 
			if(validateDatetime($validationArray['start_date']) == true){
				
			}else{
				$validationCheck = false;
			}
		}	
		
		// Enddate Validation
		if(isset($validationArray['end_date']) < isset($validationArray['start_date'])){
		
			$validationCheck = false;
		}
		
		// DurationUnit Validation
		if($validationArray['durationUnit']){
			validateDurationUnit($validationArray['durationUnit']);
		}
		
		// Color Validation
		if(isset($validationArray['color'])){
			
			// if contains hex digits and 
			if (ctype_xdigit($validationArray['color']) && substr( $validationArray['color'], 0, 0 ) === "#") {
				
			} else {
				$validationCheck = false;
			}
		}
		
		
		// ExternalID Validation
		if(isset($validationArray['externalId']) && strlen($validationArray['externalId']) > 255){
			$validationCheck = false;
		}
		
		// Status Validation
		if(isset($validationArray['status']) == 'NEW' || isset($validationArray['status']) == 'PLANED' || isset($validationArray['status']) == 'DELETED'){
			
		}else{
			$validationArray['status'] = 'NEW';
		}
		$arraylist = array($validationCheck, $validationArray);
		
		return $arraylist;
	}
/**
 * Validation especially for the Datetime
 *
 * @param datetime     $datetime       the datetime that should be validated
 *
 * @return boolean true or false. Depending on if a real datetime is given orientated to the iso standard
 */
private static function validateDatetime($datetime){
	if (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})Z$/', $datetime , $parts) == true) {
		$time = gmmktime($parts[4], $parts[5], $parts[6], $parts[2], $parts[3], $parts[1]);

		$input_time = strtotime($datetime );
		if ($input_time === false){
			return false;
		}else{
			return true;
		} 
		return true;
	} else {
		return false;
	}
}

/**
 * Validation especially for the Duration Unit
 *
 * @param string     $durationUnit       the duration unit which is about to check
 *
 * @return the new duration unit 
 */
private static function validateDurationUnit($durationUnit){
	
			if($durationUnit == 'DAYS' || $durationUnit == 'WEEKS' || $durationUnit == 'HOURS'){
				$new_durationUnit = $durationUnit;
			}else{
				$new_durationUnit = 'DAYS';
			}
		
	return $new_durationUnit;
}


// TASK 3 - TODO:
private static function durationCalculation($start_date, $end_date, $durationUnit){
	
	
	$validation = validateDatetime($start_date);
	if($validation == true){
		$validation = validateDatetime($end_date);
		if($validation == true){
			
			if(isset($end_date)){
				if($end_date > $start_date){
					
					$checked_durationUnit = validateDurationUnit($durationUnit);
					
					$duration = $start_date->diff($end_date);
								echo "difference " . $duration->y . " years, " . $duration->m." months, ".$duration->d." days ".$duration->h." hours "; 

								
					
				}
			}else{
				$duration = null;
			}
			
		}
	}
	
	return $duration;
	
}
}