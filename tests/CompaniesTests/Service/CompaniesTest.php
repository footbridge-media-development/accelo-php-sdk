<?php
	require_once __DIR__ . "/../../../vendor/autoload.php";
	require_once __DIR__ . "/../../test-env.php";

	use FootbridgeMedia\Accelo\Accelo;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\Fields;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\Filters;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\Search;
	use FootbridgeMedia\Accelo\Authentication\AuthenticationType;
	use FootbridgeMedia\Accelo\Authentication\ServiceAuthentication;
	use FootbridgeMedia\Accelo\ClientCredentials\ClientCredentials;
	use FootbridgeMedia\Accelo\Companies\Company;
	use PHPUnit\Framework\TestCase;


	final class CompaniesTest extends TestCase{
		public static Accelo $accelo;

		public static function setUpBeforeClass(): void{
			$accelo = new Accelo();

			$serviceAuthentication = new ServiceAuthentication();
			$serviceAuthentication->authType = AuthenticationType::Bearer;
			$serviceAuthentication->accessToken = TestEnv::ACCELO_SERVICE_ACCESS_TOKEN;

			$clientCredentials = new ClientCredentials();
			$clientCredentials->deploymentName = TestEnv::ACCELO_DEPLOYMENT_NAME;
			$clientCredentials->clientID = TestEnv::ACCELO_SERVICE_CLIENT_ID;
			$clientCredentials->clientSecret = TestEnv::ACCELO_SERVICE_CLIENT_SECRET;
			$accelo->setAuthentication($serviceAuthentication);
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

		public function testUpdateTestCompanyName(){

			$companyID = 11;

			$updateFields = new Fields();
			$updateFields->addField(
				fieldName: "name",
				fieldValue:"GG Test Company",
			);

			$requestResponse = self::$accelo->update(
				endpoint: "/companies/" . $companyID,
				objectType: Company::class,
				fields:$updateFields,
			);

			/** @var Company $companyUpdated */
			$companyUpdated = $requestResponse->getUpdatedObject();

			$this->assertEquals(
				expected: "GG Test Company",
				actual: $companyUpdated->name,
			);
		}
	}