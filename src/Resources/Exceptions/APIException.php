<?php
	namespace FootbridgeMedia\Resources\Exceptions;

	use Exception;

	class APIException extends Exception{
		public int $httpStatusCode;
		public string $apiStatus;
		public string $apiErrorMessage;
	}