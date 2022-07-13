<?php
	namespace FootbridgeMedia\Accelo;

	use Exception;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\AdditionalFields;
	use FootbridgeMedia\Accelo\APIRequest\RequestResponse;

	class BaseObject{
		/**
		 * @throws Exception
		 */
		public function runProgression(
			Accelo $accelo,
			int $progressionID,
			?AdditionalFields $additionalFields = null,
		): RequestResponse{
			throw new Exception("Not implemented.");
		}
	}