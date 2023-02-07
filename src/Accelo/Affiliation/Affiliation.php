<?php

	namespace FootbridgeMedia\Accelo\Affiliation;

	use FootbridgeMedia\Accelo\Address\Address;
	use FootbridgeMedia\Accelo\BaseObject;
	use FootbridgeMedia\Accelo\Companies\Company;
	use FootbridgeMedia\Accelo\Contacts\Contact;

	class Affiliation extends BaseObject {

		public int $id;
		public string $mobile;
		public string $email;
		public ?string $fax = null;
		public ?string $position = null;
		public ?string $phone = null;
		public string|Address|null $postal_address = null;
		public string|Company|null $company = null;
		public string|Contact|null $contact = null;
		public ?string $affiliation_status = null;
		public ?string $standing = null;
		public ?string $date_modified = null;
		public ?string $date_last_interacted = null;
		public ?string $staff_bookmarked = null;
		public ?string $portal_access = null;
		public ?string $communication = null;
		public ?string $invoice_method = null;

		/**
		 * @param int $affiliationID
		 * @return Affiliation
		 * @throws APICallError
		 * @throws MissingAuthorization
		 */
		public static function get(
			int $affiliationID,
		): Affiliation{
			if (self::$apiAuthorization === null){
				throw new MissingAuthorization();
			}

			$apiRequest = new APIRequest(
				host: APIRequest::API_HOST,
				method:"get",
				endpoint: sprintf("/affiliations/%d", $affiliationID),
				headers: [],
				authorization: self::$apiAuthorization,
			);

			try {
				$apiResponse = $apiRequest->call();
			}catch(Exception $e){
				throw new APICallError($e->getMessage());
			}

			if ($apiResponse->statusCode === 200){
				$rawAffiliation = json_decode($apiResponse->body, true)['response'];
				return self::fromJSON($rawAffiliation);
			}else{
				throw new Exception(sprintf("Http %d: %s", $apiResponse->statusCode, $apiResponse->body));
			}
		}

		/**
		 * Creates an affiliation between a company and a contact
		 * @param int $companyID
		 * @param int $contactID
		 * @return Affiliation
		 * @throws APICallError
		 * @throws MissingAuthorization
		 * @throws Exception
		 */
		public static function create(int $companyID, int $contactID): Affiliation{
			if (self::$apiAuthorization === null){
				throw new MissingAuthorization();
			}

			$fields = [
				"company_id"=>$companyID,
				"contact_id"=>$contactID,
			];

			$apiRequest = new APIRequest(
				host: APIRequest::API_HOST,
				method:"post",
				endpoint: "/affiliations",
				headers: [],
				authorization: self::$apiAuthorization,
			);

			$apiRequest->urlEncodedPostFields = $fields;
			$apiRequest->queryParameters = [
				"_fields"=>"contact(id),company(id),affiliation_status",
			];

			try {
				$apiResponse = $apiRequest->call();
			}catch(Exception $e){
				throw new APICallError($e->getMessage());
			}

			if ($apiResponse->statusCode === 200){
				$newAffiliation = json_decode($apiResponse->body, true)['response'];
				return self::fromJSON($newAffiliation);
			}else{
				throw new Exception(sprintf("Http %d: %s", $apiResponse->statusCode, $apiResponse->body));
			}
		}

		/**
		 * Fetches the affiliations for a company ID
		 * @throws Exception|APICallError|NoAffiliationsFound
		 */
		public static function fetchAllForCompany(int $companyID): array{

			if (self::$apiAuthorization === null){
				throw new MissingAuthorization();
			}

			$apiRequest = new APIRequest(
				host: APIRequest::API_HOST,
				method:"get",
				endpoint: "/affiliations",
				headers: [],
				authorization: self::$apiAuthorization,
			);

			$apiRequest->queryParameters = [
				"_filters"=>sprintf("company(%d)", $companyID),
				"_fields"=>"company,phone,contact,contact(firstname),contact(surname)",
			];

			try {
				$apiResponse = $apiRequest->call();
			}catch(Exception $e){
				throw new APICallError($e->getMessage());
			}

			if ($apiResponse->statusCode === 200){
				$affiliations = json_decode($apiResponse->body, true)['response'];
				if (!empty($affiliations)){
					$affiliationObjects = [];
					foreach($affiliations as $affiliationData){
						$affiliationObjects[] = self::fromJSON($affiliationData);
					}

					return $affiliationObjects;
				}else{
					throw new NoAffiliationsFound();
				}
			}else{
				throw new Exception(sprintf("Http %d: %s", $apiResponse->statusCode, $apiResponse->body));
			}
		}

		/**
		 * Attempts to find an affiliation by their email. This method will continuously call the Accelo API
		 * until an empty set is returned if the first page has results.
		 * @returns Affiliation[]
		 * @throws MissingAuthorization
		 * @throws APICallError
		 * @throws NoAffiliationsFound
		 * @throws Exception
		 */
		public static function fetchFromEmail(
			string $email,
			int $limit = 100,
			int $pageNumber = 0,
		): array{

			if (self::$apiAuthorization === null){
				throw new MissingAuthorization();
			}

			$apiRequest = new APIRequest(
				host: APIRequest::API_HOST,
				method:"get",
				endpoint: "/affiliations",
				headers: [],
				authorization: self::$apiAuthorization,
			);

			$apiRequest->queryParameters = [
				"_filters"=>sprintf("email(%s)", $email),
				"_fields"=>"standing,company(name,id),phone,contact,contact(firstname,surname,id)",
				"_limit"=>$limit,
				"_page"=>$pageNumber,
			];

			try {
				$apiResponse = $apiRequest->call();
			}catch(Exception $e){
				throw new APICallError($e->getMessage());
			}

			if ($apiResponse->statusCode === 200){
				$affiliations = json_decode($apiResponse->body, true)['response'];
				if (!empty($affiliations)){
					$affiliationObjects = [];
					foreach($affiliations as $affiliationData){
						$affiliationObjects[] = self::fromJSON($affiliationData);
					}

					if (count($affiliationObjects) === $limit){
						try{
							$moreAffiliationObjects = self::fetchFromEmail(
								$email,
								$limit,
								++$pageNumber
							);
							$affiliationObjects = array_merge($affiliationObjects, $moreAffiliationObjects);
						}catch(NoAffiliationsFound){}
					}

					return $affiliationObjects;
				}else{
					throw new NoAffiliationsFound();
				}
			}else{
				throw new Exception(sprintf("Http %d: %s", $apiResponse->statusCode, $apiResponse->body));
			}
		}

		/**
		 * @return Affiliation[]
		 * @throws MissingAuthorization
		 * @throws APICallError
		 * @throws Exception
		 */
		public static function list(
			array $filters = [],
			array $fields = [],
			?string $search = null,
			int $limit = 100,
			int $page = 0,
		): array{
			if (self::$apiAuthorization === null){
				throw new MissingAuthorization();
			}

			$payload = [];

			if (!empty($filters)){
				$payload['_filters'] = Utils::arrayToCommaSeparated($filters);
			}

			if (!empty($fields)){
				$payload['_fields'] = Utils::arrayToCommaSeparated($fields);
			}

			if (!empty($search)){
				$payload['_search'] = $search;
			}

			$payload['_limit'] = $limit;
			$payload['_page'] = $page;

			$apiRequest = new APIRequest(
				host: APIRequest::API_HOST,
				method:"get",
				endpoint: "/affiliations",
				headers: [],
				authorization: self::$apiAuthorization,
			);

			$apiRequest->queryParameters = $payload;

			try {
				$apiResponse = $apiRequest->call();
			}catch(Exception $e){
				throw new APICallError($e->getMessage());
			}

			if ($apiResponse->statusCode === 200){
				$affiliationsRawObjects = json_decode($apiResponse->body, true)['response'];
				$affiliations = [];
				foreach($affiliationsRawObjects as $affiliationsRawObject){
					$affiliations[] = self::fromJSON($affiliationsRawObject);
				}

				return $affiliations;
			}else{
				throw new Exception(sprintf("Http %d: %s", $apiResponse->statusCode, $apiResponse->body));
			}
		}

		public static function fromJSON(array $array): Affiliation{
			$affiliation = new Affiliation();
			$affiliation->id = $array['id']; // Required
			$affiliation->mobile = $array['mobile'] ?? "";
			$affiliation->phone = $array['phone'] ?? "";
			$affiliation->email = $array['email'] ?? "";
			$affiliation->standing = $array['standing'] ?? "";
			$affiliation->company = isset($array['company']) ? Company::fromJSON($array['company']) : null;
			$affiliation->contact = isset($array['contact']) ? Contact::fromJSON($array['contact']) : null;

			return $affiliation;
		}

		/**
		 * Updates the affiliation with new values provided as key->value in the array.
		 * @param array $newValues
		 * @return void
		 * @throws APICallError
		 * @throws MissingAuthorization
		 */
		public function update(array $newValues): void{
			if (self::$apiAuthorization === null){
				throw new MissingAuthorization();
			}

			$apiRequest = new APIRequest(
				host: APIRequest::API_HOST,
				method:"put",
				endpoint: sprintf("/affiliations/%d", $this->id),
				headers: [],
				authorization: self::$apiAuthorization,
			);

			$apiRequest->urlEncodedPostFields = &$newValues;

			try {
				$apiRequest->call();
			}catch(Exception $e){
				throw new APICallError($e->getMessage());
			}
		}
	}