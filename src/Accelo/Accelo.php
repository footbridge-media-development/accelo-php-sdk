<?php
	namespace FootbridgeMedia\Accelo;

	use FootbridgeMedia\Accelo\APIRequest\Paginator;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\AdditionalFields;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\Filters;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\Search;
	use FootbridgeMedia\Accelo\APIRequest\RequestResponse;
	use FootbridgeMedia\Accelo\APIRequest\RequestSender;
	use FootbridgeMedia\Accelo\Authentication\Authentication;
	use FootbridgeMedia\Accelo\ClientCredentials\ClientCredentials;
	use FootbridgeMedia\Resources\Exceptions\APIException;
	use GuzzleHttp\Exception\GuzzleException;

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
		 * Requests an authorization from a user
		 * @throws APIException
		 */
		public function getAuthorizationURL(string $scope): string{
			$requestSender = new RequestSender();
			$requestSender->clientCredentials = $this->clientCredentials;

			$requestResponse = $requestSender->getAuthorizationURL(
				scope: $scope,
			);

			return $requestResponse->response->getHeaderLine("location");
		}

		/**
		 * Uses an access code obtained from an authorization request to fetch the access tokens array.
		 * @throws APIException|GuzzleException
		 * @returns array{deployment_name: string, token_type: string, access_token: string, refresh-token: string, deployment_uri: string, deployment: string, expires_in: int, account_details:array}
		 */
		public function getTokensFromAccessCode(
			string $accessCode,
			int $expiresInSeconds,
		): array{
			$requestSender = new RequestSender();
			$requestSender->clientCredentials = $this->clientCredentials;

			$requestResponse = $requestSender->getTokensFromAccessCode(
				accessCode: $accessCode,
				expiresInSeconds: $expiresInSeconds,
			);

			$tokens = json_decode($requestResponse->responseBody, true);

			return $tokens;
		}

		/**
		 * @throws \GuzzleHttp\Exception\GuzzleException
		 * @throws APIException
		 */
		public function list(
			string $endpoint,
			string $objectType,
			?AdditionalFields $additionalFields = null,
			?Filters $filters = null,
			?Search $search = null,
			?Paginator $paginator = null,
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
				paginator: $paginator,
			);
		}
	}