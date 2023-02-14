<?php
	namespace FootbridgeMedia\Accelo;

	use FootbridgeMedia\Accelo\APIRequest\Paginator;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\AdditionalFields;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\Fields;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\Filters;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\Search;
	use FootbridgeMedia\Accelo\APIRequest\RequestResponse;
	use FootbridgeMedia\Accelo\APIRequest\RequestSender;
	use FootbridgeMedia\Accelo\Authentication\Authentication;
	use FootbridgeMedia\Accelo\ClientCredentials\ClientCredentials;
	use FootbridgeMedia\Accelo\Resources\Resource;
	use FootbridgeMedia\Resources\Exceptions\APIException;
	use GuzzleHttp\Exception\GuzzleException;

	class Accelo{
		private string $apiBaseUrl = "https://%s.api.accelo.com";
		private string $apiVersion = "v0";
		private ClientCredentials $clientCredentials;
		private Authentication $authentication;

		public function __construct(private readonly RequestSender $requestSender = new RequestSender())
		{
		}

		public function setAPIBaseUrl(string $baseURL): void{
			$this->apiBaseUrl = $baseURL;
		}

		public function getAPIBaseUrl(): string{
			return $this->apiBaseUrl;
		}

		public function setAPIVersionString(string $versionString): void{
			$this->apiVersion = $versionString;
		}

		public function getAPIVersionString(): string{
			return $this->apiVersion;
		}

		public function setCredentials(ClientCredentials $clientCredentials): void{
			$this->clientCredentials = $clientCredentials;
		}

		public function setAuthentication(Authentication $authentication): void{
			$this->authentication = $authentication;
		}

		/**
		 * Requests an authorization from a user
		 * @throws APIException
		 * @throws GuzzleException
		 */
		public function getAuthorizationURL(string $scope): string{
			$this->requestSender->setAPIBaseUrl($this->getAPIBaseUrl());
			$this->requestSender->setAPIVersionString($this->getAPIVersionString());
			$this->requestSender->clientCredentials = $this->clientCredentials;

			$requestResponse = $this->requestSender->getAuthorizationURL(
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
			$this->requestSender->setAPIBaseUrl($this->getAPIBaseUrl());
			$this->requestSender->setAPIVersionString($this->getAPIVersionString());
			$this->requestSender->clientCredentials = $this->clientCredentials;

			$requestResponse = $this->requestSender->getTokensFromAccessCode(
				accessCode: $accessCode,
				expiresInSeconds: $expiresInSeconds,
			);

			$tokens = json_decode($requestResponse->responseBody, true);

			return $tokens;
		}

		/**
		 * Authorizes this application as a service application with no individual user authenticated.
		 * @throws APIException|GuzzleException
		 * @returns array{deployment_name: string, token_type: string, access_token: string, refresh-token: string, deployment_uri: string, deployment: string, expires_in: int, account_details:array}
		 */
		public function getTokensForServiceApplication(
			string $scope,
			int $expiresInSeconds,
		): array{
			$this->requestSender->setAPIBaseUrl($this->getAPIBaseUrl());
			$this->requestSender->setAPIVersionString($this->getAPIVersionString());
			$this->requestSender->clientCredentials = $this->clientCredentials;

			$requestResponse = $this->requestSender->getTokensForServiceApplication(
				scope: $scope,
				expiresInSeconds: $expiresInSeconds,
			);

			$tokens = json_decode($requestResponse->responseBody, true);

			return $tokens;
		}

		/**
		 * @throws GuzzleException
		 * @throws APIException
		 */
		public function get(
			string $endpoint,
			string $objectType,
			?AdditionalFields $additionalFields = null,
		): RequestResponse{
			$this->requestSender->setAPIBaseUrl($this->getAPIBaseUrl());
			$this->requestSender->setAPIVersionString($this->getAPIVersionString());
			$this->requestSender->authentication = $this->authentication;
			$this->requestSender->clientCredentials = $this->clientCredentials;

			return $this->requestSender->getObject(
				objectType: $objectType,
				path: $endpoint,
				additionalFields:$additionalFields,
			);
		}

		/**
		 * @throws GuzzleException
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
			$this->requestSender->setAPIBaseUrl($this->getAPIBaseUrl());
			$this->requestSender->setAPIVersionString($this->getAPIVersionString());
			$this->requestSender->authentication = $this->authentication;
			$this->requestSender->clientCredentials = $this->clientCredentials;

			return $this->requestSender->listObjects(
				objectType: $objectType,
				path: $endpoint,
				fields:$additionalFields,
				filters:$filters,
				search: $search,
				paginator: $paginator,
			);
		}

		/**
		 * @throws APIException
		 * @throws GuzzleException
		 */
		public function update(
			string $endpoint,
			string $objectType,
			Fields $fields = null,
			?AdditionalFields $additionalFields = null,
		): RequestResponse{
			$this->requestSender->setAPIBaseUrl($this->getAPIBaseUrl());
			$this->requestSender->setAPIVersionString($this->getAPIVersionString());
			$this->requestSender->authentication = $this->authentication;
			$this->requestSender->clientCredentials = $this->clientCredentials;

			return $this->requestSender->update(
				objectType: $objectType,
				path: $endpoint,
				fields:$fields,
				additionalFields: $additionalFields,
			);
		}

		/**
		 * @throws GuzzleException
		 * @throws APIException
		 */
		public function create(
			string $endpoint,
			string $objectType,
			Fields $fields = null,
			?AdditionalFields $additionalFields = null,
		): RequestResponse{
			$this->requestSender->setAPIBaseUrl($this->getAPIBaseUrl());
			$this->requestSender->setAPIVersionString($this->getAPIVersionString());
			$this->requestSender->authentication = $this->authentication;
			$this->requestSender->clientCredentials = $this->clientCredentials;

			return $this->requestSender->create(
				objectType: $objectType,
				path: $endpoint,
				fields:$fields,
				additionalFields: $additionalFields,
			);
		}

		/**
		 * @throws GuzzleException
		 * @throws APIException
		 */
		public function delete(
			string $endpoint,
		): RequestResponse{
			$this->requestSender->setAPIBaseUrl($this->getAPIBaseUrl());
			$this->requestSender->setAPIVersionString($this->getAPIVersionString());
			$this->requestSender->authentication = $this->authentication;
			$this->requestSender->clientCredentials = $this->clientCredentials;

			return $this->requestSender->delete(
				path: $endpoint,
			);
		}

		/**
		 * @throws GuzzleException
		 * @throws APIException
		 */
		public function runProgression(
			string $endpoint,
			string $objectType,
			?AdditionalFields $additionalFields = null,
		): RequestResponse{
			$this->requestSender->setAPIBaseUrl($this->getAPIBaseUrl());
			$this->requestSender->setAPIVersionString($this->getAPIVersionString());
			$this->requestSender->authentication = $this->authentication;
			$this->requestSender->clientCredentials = $this->clientCredentials;

			return $this->requestSender->runProgression(
				objectType: $objectType,
				path: $endpoint,
				additionalFields: $additionalFields,
			);
		}

		/**
		 * @throws GuzzleException
		 * @throws APIException
		 */
		public function uploadResource(
			string $endpoint,
			string $fileName,
			string $fileContents,
		): RequestResponse{
			$this->requestSender->setAPIBaseUrl($this->getAPIBaseUrl());
			$this->requestSender->setAPIVersionString($this->getAPIVersionString());
			$this->requestSender->authentication = $this->authentication;
			$this->requestSender->clientCredentials = $this->clientCredentials;

			return $this->requestSender->uploadResource(
				objectType: Resource::class,
				path: $endpoint,
				fileName: $fileName,
				fileContents: $fileContents,
			);
		}
	}