<?php
	namespace FootbridgeMedia\Accelo;

	class Utils{
		public static function arrayToCommaSeparated(array $a){
			$counter = 0;
			$parameterValue = "";
			foreach($a as $filterName=>$filterValue){
				if (!empty($filterValue)) {
					$parameterValue .= sprintf("%s(%s)", $filterName, $filterValue);
				}else{
					$parameterValue .= $filterName;
				}
				if ($counter < count($a) - 1){
					$parameterValue .= ",";
				}
				++$counter;
			}

			return $parameterValue;
		}
	}