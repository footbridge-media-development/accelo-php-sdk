<?php
	namespace FootbridgeMedia\Accelo\APIRequest;

	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\AdditionalFields;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\Filters;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\Search;
	use FootbridgeMedia\Accelo\Authentication\Authentication;
	use FootbridgeMedia\Accelo\Authentication\WebAuthentication;
	use FootbridgeMedia\Accelo\ClientCredentials\ClientCredentials;
	use FootbridgeMedia\Resources\Exceptions\APIException;
	use GuzzleHttp\Client;
	use GuzzleHttp\RequestOptions;

	class RequestSender{
		const API_URL = "https://%s.api.accelo.com";
		const API_VERSION = "v0";

		public ClientCredentials $clientCredentials;
		public Authentication $authentication;

		private function getAPIFullURL(string $path): string{
			return sprintf(
				self::API_URL,
				$this->clientCredentials->deploymentName
			) . "/api/" . self::API_VERSION . $path;
		}

		private function getBearerAuthenticationStringFromWebToken(): string{
			/** @var WebAuthentication $webAuthentication */
			$webAuthentication = $this->authentication;
			return sprintf("Bearer %s", $webAuthentication->accessToken);
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
							if ($nameOfType === "int"){
								$object->{$property->name} = (int) $objectFromAPI[$property->name];
							}elseif ($nameOfType === "string"){
								$object->{$property->name} = (string) $objectFromAPI[$property->name];
							}elseif ($nameOfType === "array"){
								$object->{$property->name} = json_decode($objectFromAPI[$property->name], true);
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
					}elseif ($declaredType instanceof \ReflectionNamedType){
						// Property is a single type
						$nameOfType = $declaredType->getName();
						if ($nameOfType === "int"){
							$object->{$property->name} = (int) $objectFromAPI[$property->name];
						}elseif ($nameOfType === "string"){
							$object->{$property->name} = (string) $objectFromAPI[$property->name];
						}elseif ($nameOfType === "array"){
							$object->{$property->name} = json_decode($objectFromAPI[$property->name], true);
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

		/**
		 * @throws \GuzzleHttp\Exception\GuzzleException
		 * @throws APIException
		 */
		public function listObjects(
			string $objectType,
			string $path,
			?AdditionalFields $fields,
			?Filters $filters,
			?Search $search,
		): RequestResponse{

			$client = new Client();

			if ($this->authentication instanceof WebAuthentication){
				$authorizationString = $this->getBearerAuthenticationStringFromWebToken();
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

			$response = $client->request(
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
				$rateLimitResetTimestamp = (int) $headers['X-RateLimit-Reset'][0];
				$rateLimitRemaining = (int) $headers['X-RateLimit-Remaining'][0];
				$rateLimitMaxAllowedLimit = (int) $headers['X-RateLimit-Limit'][0];

				/** @var array{response: array, meta:array} $apiResponse */
				$apiResponse = json_decode($response->getBody()->getContents(), true);
				$objectsListed = $apiResponse['response'];
				$acceloObjectsParsed = [];

				/** @var array{message: string, status: string, more_info:string} $apiMeta */
				$apiMeta = $apiResponse['meta'];

				$requestResponse = new RequestResponse;
				$requestResponse->httpStatus = $statusCode;
				$requestResponse->apiStatus = $apiMeta['status'];
				$requestResponse->apiMessage = $apiMeta['message'];
				$requestResponse->apiMoreInfo = $apiMeta['more_info'];
				$requestResponse->rateLimitRemaining = $rateLimitRemaining;
				$requestResponse->rateLimitResetTimestamp = $rateLimitResetTimestamp;
				$requestResponse->rateLimitTotalMaxAllowed = $rateLimitMaxAllowedLimit;
				$requestResponse->requestType = RequestType::LIST;

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
		}
	}