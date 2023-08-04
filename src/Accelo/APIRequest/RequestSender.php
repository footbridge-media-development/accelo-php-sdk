<?php
	namespace FootbridgeMedia\Accelo\APIRequest;

	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\AdditionalFields;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\Fields;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\Filters;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\Search;
	use FootbridgeMedia\Accelo\Authentication\Authentication;
	use FootbridgeMedia\Accelo\Authentication\ServiceAuthentication;
	use FootbridgeMedia\Accelo\Authentication\WebAuthentication;
	use FootbridgeMedia\Accelo\ClientCredentials\ClientCredentials;
	use FootbridgeMedia\Accelo\Resources\Collection;
	use FootbridgeMedia\Accelo\Resources\Resource;
	use FootbridgeMedia\Resources\Exceptions\APIException;
	use GuzzleHttp\Client;
	use GuzzleHttp\Exception\ClientException;
	use GuzzleHttp\Exception\GuzzleException;
	use GuzzleHttp\RequestOptions;

	class RequestSender{
		private string $apiBaseUrl = "";
		private string $apiVersion = "";

		public ClientCredentials $clientCredentials;
		public Authentication $authentication;

		public function __construct(private readonly Client $client = new Client())
		{
		}

		private function getAPIFullURL(string $path): string{
			return sprintf(
					$this->apiBaseUrl,
					$this->clientCredentials->deploymentName
				) . "/api/" . $this->apiVersion . $path;
		}

		private function getOAuthFullURL(string $path): string{
			return sprintf(
					$this->apiBaseUrl,
					$this->clientCredentials->deploymentName
				) . "/oauth2/" . $this->apiVersion . $path;
		}

		private function getBearerAuthenticationStringFromWebToken(): string{
			/** @var WebAuthentication $webAuthentication */
			$webAuthentication = $this->authentication;
			return sprintf("Bearer %s", $webAuthentication->accessToken);
		}

		private function getBearerAuthenticationStringFromServiceToken(): string{
			/** @var ServiceAuthentication $serviceAuthentication */
			$serviceAuthentication = $this->authentication;
			return sprintf("Bearer %s", $serviceAuthentication->accessToken);
		}

		/**
		 * Takes an object and checks if any public properties of that object exist in the Accelo response array provided.
		 * If they do, then sets the object property to be the value from the API response object. Additionally,
		 * attempts to type-convert and will recursively process additional objects (Such as a Staff object found in
		 * a Task object property).
		 */
		private function hydrateObject(object $object, array $objectFromAPI): void{
			$classReflection = new \ReflectionClass($object);
			$properties = $classReflection->getProperties(
				filter: \ReflectionProperty::IS_PUBLIC,
			);

			foreach($properties as $property){
				if (array_key_exists(key:$property->name, array:$objectFromAPI)){
					$declaredType = $property->getType();
					if ($declaredType instanceof \ReflectionUnionType){
						$types = $declaredType->getTypes();
						foreach($types as $type){
							$nameOfType = $type->getName();
							if (class_exists(class: $nameOfType)){
								// Check if the API object is an object and not a string/int
								// This happens in the case where a field could be - for example - the ID of a staff
								// or the object of the staff member itself
								if (is_array($objectFromAPI[$property->name])) {
									// It's a class type
									$newObject = new $nameOfType();

									// Recursively call this same method and populate the new object
									$this->hydrateObject(
										object: $newObject,
										objectFromAPI: $objectFromAPI[$property->name],
									);
									$object->{$property->name} = $newObject;

									break;
								}
							}elseif ($nameOfType === "int"){
								$object->{$property->name} = (int) $objectFromAPI[$property->name];
								break;
							}elseif ($nameOfType === "string"){
								$object->{$property->name} = (string) $objectFromAPI[$property->name];
								break;
							}elseif ($nameOfType === "array"){
								$object->{$property->name} = $objectFromAPI[$property->name];
								break;
							}
						}
					}elseif ($declaredType instanceof \ReflectionNamedType){
						// Property is a single type
						$nameOfType = $declaredType->getName();
						if ($nameOfType === "int"){
							$object->{$property->name} = (int) $objectFromAPI[$property->name];
						}elseif ($nameOfType === "string"){
							$object->{$property->name} = (string) $objectFromAPI[$property->name];
						}elseif ($nameOfType === "array" || $nameOfType === "mixed") {
							$object->{$property->name} = $objectFromAPI[$property->name];
						}elseif (class_exists(class: $nameOfType)){
							// It's a class type
							$newObject = new $nameOfType();
							// Recursively call this same method and populate the new object
							$this->hydrateObject(
								object: $newObject,
								objectFromAPI: $objectFromAPI[$property->name],
							);
							$object->{$property->name} = $newObject;
						}
					}
				}
			}
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

		/**
		 * @throws GuzzleException
		 * @throws APIException
		 */
		public function getObject(
			string $objectType,
			string $path,
			?AdditionalFields $additionalFields,
		): RequestResponse{

			if ($this->authentication instanceof WebAuthentication){
				$authorizationString = $this->getBearerAuthenticationStringFromWebToken();
			}elseif ($this->authentication instanceof ServiceAuthentication){
				$authorizationString = $this->getBearerAuthenticationStringFromServiceToken();
			}

			$queryParameters = [];

			// Handle _fields
			if ($additionalFields !== null){
				$fieldsAsCommaSeparatedString = $additionalFields->getFieldsAsCommaSeparatedList();
				if (!empty($fieldsAsCommaSeparatedString)){
					$queryParameters['_fields'] = $fieldsAsCommaSeparatedString;
				}
			}

			$response = $this->client->request(
				method:"GET",
				uri: $this->getAPIFullURL($path),
				options:[
					RequestOptions::HEADERS => [
						"Authorization"=>$authorizationString,
					],
					RequestOptions::QUERY => $queryParameters,
				],
			);

			$statusCode = $response->getStatusCode();
			if ($statusCode === 200){

				$headers = $response->getHeaders();
				$responseBody = $response->getBody()->getContents();
				$rateLimitResetTimestamp = (int) $headers['X-RateLimit-Reset'][0];
				$rateLimitRemaining = (int) $headers['X-RateLimit-Remaining'][0];
				$rateLimitMaxAllowedLimit = (int) $headers['X-RateLimit-Limit'][0];

				/** @var array{response: array, meta:array} $apiResponse */
				$apiResponse = json_decode($responseBody, true);
				$returnedAcceloObject = $apiResponse['response'];

				/** @var array{message: string, status: string, more_info:string} $apiMeta */
				$apiMeta = $apiResponse['meta'];

				$requestResponse = new RequestResponse;
				$requestResponse->responseBody = $responseBody;
				$requestResponse->httpStatus = $statusCode;
				$requestResponse->apiStatus = $apiMeta['status'];
				$requestResponse->apiMessage = $apiMeta['message'];
				$requestResponse->apiMoreInfo = $apiMeta['more_info'];
				$requestResponse->rateLimitRemaining = $rateLimitRemaining;
				$requestResponse->rateLimitResetTimestamp = $rateLimitResetTimestamp;
				$requestResponse->rateLimitTotalMaxAllowed = $rateLimitMaxAllowedLimit;
				$requestResponse->requestType = RequestType::GET_OBJECT;

				$newAcceloObject = new $objectType;
				$this->hydrateObject(
					object: $newAcceloObject,
					objectFromAPI: $returnedAcceloObject,
				);

				$requestResponse->setFetchedObject($newAcceloObject);

				return $requestResponse;
			}else{
				$responseBody = $response->getBody()->getContents();

				/** @var array{response: null, meta:array} $apiResponse */
				$apiResponse = json_decode($responseBody, true);
				/** @var array{message: string, status: string, more_info:string} $apiMeta */
				$apiMeta = $apiResponse['meta'];
				$apiException = new APIException;
				$apiException->apiErrorMessage = $apiMeta['message'];
				$apiException->apiStatus = $apiMeta['status'];
				$apiException->httpStatusCode = $statusCode;
				throw $apiException;
			}
		}

		/**
		 * @throws GuzzleException
		 * @throws APIException
		 */
		public function listObjects(
			string $objectType,
			string $path,
			?AdditionalFields $fields,
			?Filters $filters,
			?Search $search,
			?Paginator $paginator,
		): RequestResponse{

			if ($this->authentication instanceof WebAuthentication){
				$authorizationString = $this->getBearerAuthenticationStringFromWebToken();
			}elseif ($this->authentication instanceof ServiceAuthentication){
				$authorizationString = $this->getBearerAuthenticationStringFromServiceToken();
			}

			$queryParameters = [];

			// Handle _fields
			if ($fields !== null){
				$fieldsAsCommaSeparatedString = $fields->getFieldsAsCommaSeparatedList();
				if (!empty($fieldsAsCommaSeparatedString)){
					$queryParameters['_fields'] = $fieldsAsCommaSeparatedString;
				}
			}

			// Handle _filters
			if ($filters !== null){
				$filtersAsCommaSeparatedString = $filters->getFiltersAsCommaSeparatedString();
				if (!empty($filtersAsCommaSeparatedString)){
					$queryParameters['_filters'] = $filtersAsCommaSeparatedString;
				}
			}

			// Handle _search
			if ($search !== null){
				if (!empty($search->getQuery())){
					$queryParameters['_search'] = $search->getQuery();
				}
			}

			// Handle pagination
			$returnLimit = Paginator::DEFAULT_LIMIT; // Used to tell the RequestResponse if there are more results
			if ($paginator !== null){
				$returnLimit = $paginator->getLimit();
				$queryParameters['_page'] = $paginator->getPage();
				$queryParameters['_limit'] = $paginator->getLimit();
			}

			$response = $this->client->request(
				method:"GET",
				uri: $this->getAPIFullURL($path),
				options:[
					RequestOptions::HEADERS => [
						"Authorization"=>$authorizationString,
					],
					RequestOptions::QUERY => $queryParameters,
				],
			);

			$statusCode = $response->getStatusCode();
			if ($statusCode === 200){

				$headers = $response->getHeaders();
				$responseBody = $response->getBody()->getContents();
				$rateLimitResetTimestamp = (int) $headers['X-RateLimit-Reset'][0];
				$rateLimitRemaining = (int) $headers['X-RateLimit-Remaining'][0];
				$rateLimitMaxAllowedLimit = (int) $headers['X-RateLimit-Limit'][0];

				/** @var array{response: array, meta:array} $apiResponse */
				$apiResponse = json_decode($responseBody, true);
				$objectsListed = $apiResponse['response'];

				/**
				 * 2/8/2023
				 * Handle an discrepency in the API for collections. Listing collections
				 * will instead have a key in the response called "collections" that houses the
				 * collections.
				 */
				if (array_key_exists("collections", $objectsListed)){
					$objectsListed = $objectsListed['collections'];
				}
				/**
				 * Same as above for periods.
				 */
				elseif (array_key_exists("periods", $objectsListed)){
					$objectsListed = $objectsListed['periods'];
				}

				$acceloObjectsParsed = [];

				/** @var array{message: string, status: string, more_info:string} $apiMeta */
				$apiMeta = $apiResponse['meta'];

				$requestResponse = new RequestResponse;
				$requestResponse->responseBody = $responseBody;
				$requestResponse->httpStatus = $statusCode;
				$requestResponse->apiStatus = $apiMeta['status'];
				$requestResponse->apiMessage = $apiMeta['message'];
				$requestResponse->apiMoreInfo = $apiMeta['more_info'];
				$requestResponse->rateLimitRemaining = $rateLimitRemaining;
				$requestResponse->rateLimitResetTimestamp = $rateLimitResetTimestamp;
				$requestResponse->rateLimitTotalMaxAllowed = $rateLimitMaxAllowedLimit;
				$requestResponse->requestType = RequestType::LIST;

				if (count($objectsListed) >= $returnLimit){
					// There _could_ be more results
					$requestResponse->hasMorePages = true;
				}else{
					$requestResponse->hasMorePages = false;
				}

				foreach($objectsListed as $objectFromAPI){
					$newAcceloObject = new $objectType;
					$this->hydrateObject(
						object: $newAcceloObject,
						objectFromAPI: $objectFromAPI,
					);

					$acceloObjectsParsed[] = $newAcceloObject;
				}

				$requestResponse->setListRequest($acceloObjectsParsed);

				return $requestResponse;
			}else{
				$responseBody = $response->getBody()->getContents();

				/** @var array{response: null, meta:array} $apiResponse */
				$apiResponse = json_decode($responseBody, true);
				/** @var array{message: string, status: string, more_info:string} $apiMeta */
				$apiMeta = $apiResponse['meta'];
				$apiException = new APIException;
				$apiException->apiErrorMessage = $apiMeta['message'];
				$apiException->apiStatus = $apiMeta['status'];
				$apiException->httpStatusCode = $statusCode;
				throw $apiException;
			}
		}

		/**
		 * @throws GuzzleException
		 * @throws APIException
		 */
		public function update(
			string $objectType,
			string $path,
			?Fields $fields,
			?AdditionalFields $additionalFields,
		): RequestResponse{

			if ($this->authentication instanceof WebAuthentication){
				$authorizationString = $this->getBearerAuthenticationStringFromWebToken();
			}elseif ($this->authentication instanceof ServiceAuthentication){
				$authorizationString = $this->getBearerAuthenticationStringFromServiceToken();
			}

			$formParams = $fields->getFieldsAsKeyValuePairs();

			// Handle _fields
			if ($additionalFields !== null){
				$fieldsAsCommaSeparatedString = $additionalFields->getFieldsAsCommaSeparatedList();
				if (!empty($fieldsAsCommaSeparatedString)){
					$formParams['_fields'] = $fieldsAsCommaSeparatedString;
				}
			}

			$response = $this->client->request(
				method:"PUT",
				uri: $this->getAPIFullURL($path),
				options:[
					RequestOptions::HEADERS => [
						"Authorization"=>$authorizationString,
					],
					RequestOptions::FORM_PARAMS => $formParams,
				],
			);

			$statusCode = $response->getStatusCode();
			if ($statusCode === 200){

				$headers = $response->getHeaders();
				$responseBody = $response->getBody()->getContents();
				$rateLimitResetTimestamp = (int) $headers['X-RateLimit-Reset'][0];
				$rateLimitRemaining = (int) $headers['X-RateLimit-Remaining'][0];
				$rateLimitMaxAllowedLimit = (int) $headers['X-RateLimit-Limit'][0];

				/** @var array{response: array, meta:array} $apiResponse */
				$apiResponse = json_decode($responseBody, true);
				$updatedObjectReturned = $apiResponse['response'];

				/** @var array{message: string, status: string, more_info:string} $apiMeta */
				$apiMeta = $apiResponse['meta'];

				$requestResponse = new RequestResponse;
				$requestResponse->responseBody = $responseBody;
				$requestResponse->httpStatus = $statusCode;
				$requestResponse->apiStatus = $apiMeta['status'];
				$requestResponse->apiMessage = $apiMeta['message'];
				$requestResponse->apiMoreInfo = $apiMeta['more_info'];
				$requestResponse->rateLimitRemaining = $rateLimitRemaining;
				$requestResponse->rateLimitResetTimestamp = $rateLimitResetTimestamp;
				$requestResponse->rateLimitTotalMaxAllowed = $rateLimitMaxAllowedLimit;
				$requestResponse->requestType = RequestType::UPDATE;

				$newAcceloObject = new $objectType;
				$this->hydrateObject(
					object: $newAcceloObject,
					objectFromAPI: $updatedObjectReturned,
				);

				$requestResponse->setUpdatedObject($newAcceloObject);

				return $requestResponse;
			}else{
				$responseBody = $response->getBody()->getContents();

				/** @var array{response: null, meta:array} $apiResponse */
				$apiResponse = json_decode($responseBody, true);
				/** @var array{message: string, status: string, more_info:string} $apiMeta */
				$apiMeta = $apiResponse['meta'];
				$apiException = new APIException;
				$apiException->apiErrorMessage = $apiMeta['message'];
				$apiException->apiStatus = $apiMeta['status'];
				$apiException->httpStatusCode = $statusCode;
				throw $apiException;
			}
		}

		/**
		 * @throws GuzzleException
		 * @throws APIException
		 */
		public function create(
			string $objectType,
			string $path,
			?Fields $fields,
			?AdditionalFields $additionalFields,
		): RequestResponse{

			if ($this->authentication instanceof WebAuthentication){
				$authorizationString = $this->getBearerAuthenticationStringFromWebToken();
			}elseif ($this->authentication instanceof ServiceAuthentication){
				$authorizationString = $this->getBearerAuthenticationStringFromServiceToken();
			}

			$formParams = $fields->getFieldsAsKeyValuePairs();

			// Handle _fields
			if ($additionalFields !== null){
				$fieldsAsCommaSeparatedString = $additionalFields->getFieldsAsCommaSeparatedList();
				if (!empty($fieldsAsCommaSeparatedString)){
					$formParams['_fields'] = $fieldsAsCommaSeparatedString;
				}
			}

			$response = $this->client->request(
				method:"POST",
				uri: $this->getAPIFullURL($path),
				options:[
					RequestOptions::HEADERS => [
						"Authorization"=>$authorizationString,
					],
					RequestOptions::FORM_PARAMS => $formParams,
				],
			);

			$statusCode = $response->getStatusCode();
			if ($statusCode === 200){

				$headers = $response->getHeaders();
				$responseBody = $response->getBody()->getContents();
				$rateLimitResetTimestamp = (int) $headers['X-RateLimit-Reset'][0];
				$rateLimitRemaining = (int) $headers['X-RateLimit-Remaining'][0];
				$rateLimitMaxAllowedLimit = (int) $headers['X-RateLimit-Limit'][0];

				/** @var array{response: array, meta:array} $apiResponse */
				$apiResponse = json_decode($responseBody, true);
				$updatedObjectReturned = $apiResponse['response'];

				/** @var array{message: string, status: string, more_info:string} $apiMeta */
				$apiMeta = $apiResponse['meta'];

				$requestResponse = new RequestResponse;
				$requestResponse->responseBody = $responseBody;
				$requestResponse->httpStatus = $statusCode;
				$requestResponse->apiStatus = $apiMeta['status'];
				$requestResponse->apiMessage = $apiMeta['message'];
				$requestResponse->apiMoreInfo = $apiMeta['more_info'];
				$requestResponse->rateLimitRemaining = $rateLimitRemaining;
				$requestResponse->rateLimitResetTimestamp = $rateLimitResetTimestamp;
				$requestResponse->rateLimitTotalMaxAllowed = $rateLimitMaxAllowedLimit;
				$requestResponse->requestType = RequestType::CREATE;

				$newAcceloObject = new $objectType;
				$this->hydrateObject(
					object: $newAcceloObject,
					objectFromAPI: $updatedObjectReturned,
				);

				$requestResponse->setCreatedObject($newAcceloObject);

				return $requestResponse;
			}else{
				$responseBody = $response->getBody()->getContents();

				/** @var array{response: null, meta:array} $apiResponse */
				$apiResponse = json_decode($responseBody, true);
				/** @var array{message: string, status: string, more_info:string} $apiMeta */
				$apiMeta = $apiResponse['meta'];
				$apiException = new APIException;
				$apiException->apiErrorMessage = $apiMeta['message'];
				$apiException->apiStatus = $apiMeta['status'];
				$apiException->httpStatusCode = $statusCode;
				throw $apiException;
			}
		}

		/**
		 * @throws GuzzleException
		 * @throws APIException
		 */
		public function delete(
			string $path,
		): RequestResponse{

			if ($this->authentication instanceof WebAuthentication){
				$authorizationString = $this->getBearerAuthenticationStringFromWebToken();
			}elseif ($this->authentication instanceof ServiceAuthentication){
				$authorizationString = $this->getBearerAuthenticationStringFromServiceToken();
			}

			$response = $this->client->request(
				method:"DELETE",
				uri: $this->getAPIFullURL($path),
				options:[
					RequestOptions::HEADERS => [
						"Authorization"=>$authorizationString,
					],
				],
			);

			$statusCode = $response->getStatusCode();
			if ($statusCode === 200){

				$headers = $response->getHeaders();
				$responseBody = $response->getBody()->getContents();
				$rateLimitResetTimestamp = (int) $headers['X-RateLimit-Reset'][0];
				$rateLimitRemaining = (int) $headers['X-RateLimit-Remaining'][0];
				$rateLimitMaxAllowedLimit = (int) $headers['X-RateLimit-Limit'][0];

				/** @var array{meta:array} $apiResponse */
				$apiResponse = json_decode($responseBody, true);

				/** @var array{message: string, status: string, more_info:string} $apiMeta */
				$apiMeta = $apiResponse['meta'];

				$requestResponse = new RequestResponse;
				$requestResponse->responseBody = $responseBody;
				$requestResponse->httpStatus = $statusCode;
				$requestResponse->apiStatus = $apiMeta['status'];
				$requestResponse->apiMessage = $apiMeta['message'];
				$requestResponse->apiMoreInfo = $apiMeta['more_info'];
				$requestResponse->rateLimitRemaining = $rateLimitRemaining;
				$requestResponse->rateLimitResetTimestamp = $rateLimitResetTimestamp;
				$requestResponse->rateLimitTotalMaxAllowed = $rateLimitMaxAllowedLimit;
				$requestResponse->requestType = RequestType::DELETE;

				return $requestResponse;
			}else{
				$responseBody = $response->getBody()->getContents();

				/** @var array{meta:array} $apiResponse */
				$apiResponse = json_decode($responseBody, true);
				/** @var array{message: string, status: string, more_info:string} $apiMeta */
				$apiMeta = $apiResponse['meta'];
				$apiException = new APIException;
				$apiException->apiErrorMessage = $apiMeta['message'];
				$apiException->apiStatus = $apiMeta['status'];
				$apiException->httpStatusCode = $statusCode;
				throw $apiException;
			}
		}

		/**
		 * @throws GuzzleException
		 * @throws APIException
		 */
		public function runProgression(
			string $objectType,
			string $path,
			?AdditionalFields $additionalFields = null,
		): RequestResponse{

			if ($this->authentication instanceof WebAuthentication){
				$authorizationString = $this->getBearerAuthenticationStringFromWebToken();
			}elseif ($this->authentication instanceof ServiceAuthentication){
				$authorizationString = $this->getBearerAuthenticationStringFromServiceToken();
			}

			$formParams = [];
			if (!empty($additionalFields)){
				$formParams['_fields'] = $additionalFields->getFieldsAsCommaSeparatedList();
			}

			$response = $this->client->request(
				method:"POST",
				uri: $this->getAPIFullURL($path),
				options:[
					RequestOptions::HEADERS => [
						"Authorization"=>$authorizationString,
					],
					RequestOptions::FORM_PARAMS => $formParams,
				],
			);

			$statusCode = $response->getStatusCode();
			if ($statusCode === 200){

				$headers = $response->getHeaders();
				$responseBody = $response->getBody()->getContents();
				$rateLimitResetTimestamp = (int) $headers['X-RateLimit-Reset'][0];
				$rateLimitRemaining = (int) $headers['X-RateLimit-Remaining'][0];
				$rateLimitMaxAllowedLimit = (int) $headers['X-RateLimit-Limit'][0];

				/** @var array{response: array, meta:array} $apiResponse */
				$apiResponse = json_decode($responseBody, true);
				$progressedObjectReturned = $apiResponse['response'];

				/** @var array{message: string, status: string, more_info:string} $apiMeta */
				$apiMeta = $apiResponse['meta'];

				$requestResponse = new RequestResponse;
				$requestResponse->responseBody = $responseBody;
				$requestResponse->httpStatus = $statusCode;
				$requestResponse->apiStatus = $apiMeta['status'];
				$requestResponse->apiMessage = $apiMeta['message'];
				$requestResponse->apiMoreInfo = $apiMeta['more_info'];
				$requestResponse->rateLimitRemaining = $rateLimitRemaining;
				$requestResponse->rateLimitResetTimestamp = $rateLimitResetTimestamp;
				$requestResponse->rateLimitTotalMaxAllowed = $rateLimitMaxAllowedLimit;
				$requestResponse->requestType = RequestType::RUN_PROGRESSION;

				$newAcceloObject = new $objectType;
				$this->hydrateObject(
					object: $newAcceloObject,
					objectFromAPI: $progressedObjectReturned,
				);

				$requestResponse->setProgressedObject($newAcceloObject);

				return $requestResponse;
			}else{
				$responseBody = $response->getBody()->getContents();

				/** @var array{response: null, meta:array} $apiResponse */
				$apiResponse = json_decode($responseBody, true);
				/** @var array{message: string, status: string, more_info:string} $apiMeta */
				$apiMeta = $apiResponse['meta'];
				$apiException = new APIException;
				$apiException->apiErrorMessage = $apiMeta['message'];
				$apiException->apiStatus = $apiMeta['status'];
				$apiException->httpStatusCode = $statusCode;
				throw $apiException;
			}
		}

		/**
		 * @throws GuzzleException
		 * @throws APIException
		 */
		public function uploadResource(
			string $objectType,
			string $path,
			string $fileName,
			string $fileContents,
		): RequestResponse{

			if ($this->authentication instanceof WebAuthentication){
				$authorizationString = $this->getBearerAuthenticationStringFromWebToken();
			}elseif ($this->authentication instanceof ServiceAuthentication){
				$authorizationString = $this->getBearerAuthenticationStringFromServiceToken();
			}

			$response = $this->client->request(
				method:"POST",
				uri: $this->getAPIFullURL($path),
				options:[
					RequestOptions::HEADERS => [
						"Authorization"=>$authorizationString,
					],
					RequestOptions::MULTIPART => [
						[
							"name"=>"resource",
							"contents"=>$fileContents,
							"filename"=>$fileName,
						],
					],
				],
			);

			$statusCode = $response->getStatusCode();
			if ($statusCode === 200){

				$headers = $response->getHeaders();
				$responseBody = $response->getBody()->getContents();
				$rateLimitResetTimestamp = (int) $headers['X-RateLimit-Reset'][0];
				$rateLimitRemaining = (int) $headers['X-RateLimit-Remaining'][0];
				$rateLimitMaxAllowedLimit = (int) $headers['X-RateLimit-Limit'][0];

				/** @var array{response: array, meta:array} $apiResponse */
				$apiResponse = json_decode($responseBody, true);
				$uploadedObjectReturned = $apiResponse['response'];

				// Weird discrepency where there is a "resource" key in the array
				// when uploading a resource that houses the resource
				if (array_key_exists("resource", $uploadedObjectReturned)){
					$uploadedObjectReturned = $uploadedObjectReturned['resource'];
				}

				/** @var array{message: string, status: string, more_info:string} $apiMeta */
				$apiMeta = $apiResponse['meta'];

				$requestResponse = new RequestResponse;
				$requestResponse->responseBody = $responseBody;
				$requestResponse->httpStatus = $statusCode;
				$requestResponse->apiStatus = $apiMeta['status'];
				$requestResponse->apiMessage = $apiMeta['message'];
				$requestResponse->apiMoreInfo = $apiMeta['more_info'];
				$requestResponse->rateLimitRemaining = $rateLimitRemaining;
				$requestResponse->rateLimitResetTimestamp = $rateLimitResetTimestamp;
				$requestResponse->rateLimitTotalMaxAllowed = $rateLimitMaxAllowedLimit;
				$requestResponse->requestType = RequestType::UPLOAD_RESOURCE;

				$newAcceloObject = new $objectType;
				$this->hydrateObject(
					object: $newAcceloObject,
					objectFromAPI: $uploadedObjectReturned,
				);

				$requestResponse->setUploadedObject($newAcceloObject);

				return $requestResponse;
			}else{
				$responseBody = $response->getBody()->getContents();

				/** @var array{response: null, meta:array} $apiResponse */
				$apiResponse = json_decode($responseBody, true);
				/** @var array{message: string, status: string, more_info:string} $apiMeta */
				$apiMeta = $apiResponse['meta'];
				$apiException = new APIException;
				$apiException->apiErrorMessage = $apiMeta['message'];
				$apiException->apiStatus = $apiMeta['status'];
				$apiException->httpStatusCode = $statusCode;
				throw $apiException;
			}
		}

		/**
		 * @throws APIException|GuzzleException
		 */
		public function getAuthorizationURL(
			string $scope,
		): RequestResponse{

			$clientID = $this->clientCredentials->clientID;

			$postParameters = [
				"client_id"=>$clientID,
				"response_type"=>"code",
				"scope"=>$scope,
			];

			try {
				$response = $this->client->request(
					method: "POST",
					uri: $this->getOAuthFullURL("/authorize"),
					options: [
						RequestOptions::ALLOW_REDIRECTS => false, // So the Location header is present
						RequestOptions::FORM_PARAMS => $postParameters,
					],
				);

				$statusCode = $response->getStatusCode();
				$responseBody = $response->getBody()->getContents();
				$requestResponse = new RequestResponse;
				$requestResponse->response = $response;
				$requestResponse->httpStatus = $statusCode;
				$requestResponse->responseBody = $responseBody;
				$requestResponse->requestType = RequestType::GET_AUTHORIZATION_URL;

				return $requestResponse;
			}catch(ClientException $e){
				$jsonData = $e->getResponse()->getBody()->getContents();
				/** @var array{error_description: string, error: string} $errorData */
				$errorData = json_decode($jsonData, true);

				$apiException = new APIException;
				$apiException->apiErrorMessage = $errorData['error_description'];
				$apiException->apiStatus = $errorData['error'];
				$apiException->httpStatusCode = $e->getResponse()->getStatusCode();
				throw $apiException;
			}
		}

		/**
		 * @throws APIException
		 * @throws GuzzleException
		 */
		public function getTokensFromAccessCode(
			string $accessCode,
			int $expiresInSeconds,
		): RequestResponse{

			$clientID = $this->clientCredentials->clientID;
			$clientSecret = $this->clientCredentials->clientSecret;
			$basicAuthentication = sprintf(
				"Basic %s",
				base64_encode(
					sprintf(
						"%s:%s",
						$clientID,
						$clientSecret,
					),
				),
			);

			$postParameters = [
				"grant_type"=>"authorization_code",
				"code"=>$accessCode,
				"expires_in"=>$expiresInSeconds,
			];

			try {
				$response = $this->client->request(
					method: "POST",
					uri: $this->getOAuthFullURL("/token"),
					options: [
						RequestOptions::HEADERS => [
							"Authorization" => $basicAuthentication,
						],
						RequestOptions::FORM_PARAMS => $postParameters,
					],
				);

				$statusCode = $response->getStatusCode();
				if ($statusCode === 200) {

					$responseBody = $response->getBody()->getContents();
					$requestResponse = new RequestResponse;
					$requestResponse->httpStatus = $statusCode;
					$requestResponse->responseBody = $responseBody;
					$requestResponse->requestType = RequestType::GET_TOKENS_FROM_ACCESS_CODE;

					return $requestResponse;
				} else {
					/** @var array{response: null, meta:array} $apiResponse */
					$apiResponse = json_decode($response->getBody()->getContents(), true);
					/** @var array{message: string, status: string, more_info:string} $apiMeta */
					$apiMeta = $apiResponse['meta'];
					$apiException = new APIException;
					$apiException->apiErrorMessage = $apiMeta['message'];
					$apiException->apiStatus = $apiMeta['status'];
					$apiException->httpStatusCode = $statusCode;
					throw $apiException;
				}
			}catch(ClientException $e){
				$jsonData = $e->getResponse()->getBody()->getContents();
				/** @var array{error_description: string, error: string} $errorData */
				$errorData = json_decode($jsonData, true);

				$apiException = new APIException;
				$apiException->apiErrorMessage = $errorData['error_description'];
				$apiException->apiStatus = $errorData['error'];
				$apiException->httpStatusCode = $e->getResponse()->getStatusCode();
				throw $apiException;
			}
		}

		/**
		 * @throws GuzzleException
		 * @throws APIException
		 */
		public function getTokensForServiceApplication(
			string $scope,
			int $expiresInSeconds,
		): RequestResponse{
			$clientID = $this->clientCredentials->clientID;
			$clientSecret = $this->clientCredentials->clientSecret;
			$basicAuthentication = sprintf(
				"Basic %s",
				base64_encode(
					sprintf(
						"%s:%s",
						$clientID,
						$clientSecret,
					),
				),
			);

			$postParameters = [
				"grant_type"=>"client_credentials",
				"scope"=>$scope,
				"expires_in"=>$expiresInSeconds,
			];

			try {
				$response = $this->client->request(
					method: "POST",
					uri: $this->getOAuthFullURL("/token"),
					options: [
						RequestOptions::HEADERS => [
							"Authorization" => $basicAuthentication,
						],
						RequestOptions::FORM_PARAMS => $postParameters,
					],
				);

				$statusCode = $response->getStatusCode();
				if ($statusCode === 200) {

					$responseBody = $response->getBody()->getContents();
					$requestResponse = new RequestResponse;
					$requestResponse->httpStatus = $statusCode;
					$requestResponse->responseBody = $responseBody;
					$requestResponse->requestType = RequestType::GET_TOKENS_FROM_ACCESS_CODE;

					return $requestResponse;
				} else {
					/** @var array{response: null, meta:array} $apiResponse */
					$apiResponse = json_decode($response->getBody()->getContents(), true);
					/** @var array{message: string, status: string, more_info:string} $apiMeta */
					$apiMeta = $apiResponse['meta'];
					$apiException = new APIException;
					$apiException->apiErrorMessage = $apiMeta['message'];
					$apiException->apiStatus = $apiMeta['status'];
					$apiException->httpStatusCode = $statusCode;
					throw $apiException;
				}
			}catch(ClientException $e){
				$jsonData = $e->getResponse()->getBody()->getContents();
				/** @var array{error_description: string, error: string} $errorData */
				$errorData = json_decode($jsonData, true);

				$apiException = new APIException;
				$apiException->apiErrorMessage = $errorData['error_description'];
				$apiException->apiStatus = $errorData['error'];
				$apiException->httpStatusCode = $e->getResponse()->getStatusCode();
				throw $apiException;
			}
		}
	}