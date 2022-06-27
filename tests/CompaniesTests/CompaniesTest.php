<?php
	require_once __DIR__ . "/../../vendor/autoload.php";
	require_once __DIR__ . "/../test-env.php";

	use FootbridgeMedia\Accelo\Accelo;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\AdditionalFields;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\Filters;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\Search;
	use FootbridgeMedia\Accelo\Authentication\AuthenticationType;
	use FootbridgeMedia\Accelo\Authentication\WebAuthentication;
	use FootbridgeMedia\Accelo\ClientCredentials\ClientCredentials;
	use FootbridgeMedia\Accelo\Companies\Company;
	use FootbridgeMedia\Accelo\Companies\Segmentation;
	use FootbridgeMedia\Accelo\Profiles\ProfileFieldType;
	use FootbridgeMedia\Accelo\Profiles\ProfileValue;
	use PHPUnit\Framework\TestCase;


	final class CompaniesTest extends TestCase{
		public static Accelo $accelo;

		public static function setUpBeforeClass(): void{
			$accelo = new Accelo();

			$webAuthentication = new WebAuthentication();
			$webAuthentication->authType = AuthenticationType::Bearer;
			$webAuthentication->accessToken = TestEnv::ACCELO_USER_ACCESS_TOKEN;
			$webAuthentication->refreshToken = TestEnv::ACCELO_USER_REFRESH_TOKEN;

			$clientCredentials = new ClientCredentials();
			$clientCredentials->deploymentName = TestEnv::ACCELO_DEPLOYMENT_NAME;
			$clientCredentials->clientID = TestEnv::ACCELO_WEB_CLIENT_ID;
			$clientCredentials->clientSecret = TestEnv::ACCELO_WEB_CLIENT_SECRET;
			$accelo->setAuthentication($webAuthentication);
			$accelo->setCredentials($clientCredentials);

			self::$accelo = $accelo;
		}

		public function testListCompaniesByName(){

			$search = new Search();
			$search->setQuery("Footbridge Media");

			$filters = new Filters();
			$filters->addFilter(
				filterName:"standing",
				filterValue: "active",
			);

			$requestResponse = self::$accelo->list(
				endpoint: "/companies",
				objectType: Company::class,
				filters: $filters,
				search: $search,
			);

			/** @var Company[] $companies */
			$companies = $requestResponse->getListResult();

			$this->assertCount(
				expectedCount: 1,
				haystack:$companies,
			);

			$company = $companies[0];

			$this->assertInstanceOf(
				expected: Company::class,
				actual:$company,
			);

			$this->assertEquals(
				expected:"Footbridge Media",
				actual:$company->name,
			);
		}

		public function testListCompanySegmentations(){

			$search = new Search();
			$search->setQuery("Footbridge Media");

			$filters = new Filters();
			$filters->addFilter(
				filterName:"standing",
				filterValue: "active",
			);

			$requestResponse = self::$accelo->list(
				endpoint: "/companies",
				objectType: Company::class,
				filters: $filters,
				search: $search,
			);

			/** @var Company[] $companies */
			$companies = $requestResponse->getListResult();

			$this->assertCount(
				expectedCount: 1,
				haystack:$companies,
			);

			$company = $companies[0];

			$segmentationResponse = self::$accelo->list(
				endpoint: sprintf("/companies/%d/segmentations", $company->id),
				objectType: Segmentation::class,
			);

			/** @var Segmentation[] $segmentations */
			$segmentations = $segmentationResponse->getListResult();
		}

		public function testListProfileValues(){

			$search = new Search();
			$search->setQuery("Footbridge Media");

			$filters = new Filters();
			$filters->addFilter(
				filterName:"standing",
				filterValue: "active",
			);

			$requestResponse = self::$accelo->list(
				endpoint: "/companies",
				objectType: Company::class,
				filters: $filters,
				search: $search,
			);

			/** @var Company[] $companies */
			$companies = $requestResponse->getListResult();

			$this->assertCount(
				expectedCount: 1,
				haystack:$companies,
			);

			$company = $companies[0];

			// Tell it to return all possible fields for the ProfileValue object API
			$profileValuesFields = new AdditionalFields();
			$profileValuesFields->addField(
				fieldName:"_ALL",
			);

			$profileValuesResponse = self::$accelo->list(
				endpoint: sprintf("/companies/%d/profiles/values", $company->id),
				objectType: ProfileValue::class,
				additionalFields: $profileValuesFields,
			);

			/** @var ProfileValue[] $profileValues */
			$profileValues = $profileValuesResponse->getListResult();

			foreach($profileValues as $profileValue){
				if ($profileValue->field_type === ProfileFieldType::MULTI_SELECT->value) {
					print("{$profileValue->field_name}\n");
					var_dump($profileValue->field_id);
					var_dump($profileValue->values);
				}
			}
		}
	}