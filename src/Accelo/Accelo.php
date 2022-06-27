<?php
	namespace FootbridgeMedia\Accelo;

	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\AdditionalFields;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\Filters;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\Search;
	use FootbridgeMedia\Accelo\APIRequest\RequestResponse;
	use FootbridgeMedia\Accelo\APIRequest\RequestSender;
	use FootbridgeMedia\Accelo\Authentication\Authentication;
	use FootbridgeMedia\Accelo\ClientCredentials\ClientCredentials;

	class Accelo{
		private ClientCredentials $clientCredentials;
		private Authentication $authentication;

		public function setCredentials(ClientCredentials $clientCredentials): void{
			$this->clientCredentials = $clientCredentials;
		}

		public function setAuthentication(Authentication $authentication): void{
			$this->authentication = $authentication;
		}

		/**
		 * @throws \GuzzleHttp\Exception\GuzzleException
		 * @throws \FootbridgeMedia\Resources\Exceptions\APIException
		 */
		public function list(
			string $endpoint,
			string $objectType,
			?AdditionalFields $additionalFields = null,
			?Filters $filters = null,
			?Search $search = null,
		): RequestResponse{
			$requestSender = new RequestSender();
			$requestSender->authentication = $this->authentication;
			$requestSender->clientCredentials = $this->clientCredentials;

			return $requestSender->listObjects(
				objectType: $objectType,
				path: $endpoint,
				fields:$additionalFields,
				filters:$filters,
				search: $search,
			);
		}
	}