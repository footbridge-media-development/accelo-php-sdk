<?php
	require_once __DIR__ . "/../../vendor/autoload.php";
	require_once __DIR__ . "/../test-env.php";

	use FootbridgeMedia\Accelo\Accelo;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\Filters;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\Search;
	use FootbridgeMedia\Accelo\Authentication\AuthenticationType;
	use FootbridgeMedia\Accelo\Authentication\WebAuthentication;
	use FootbridgeMedia\Accelo\ClientCredentials\ClientCredentials;
	use FootbridgeMedia\Accelo\Companies\Company;
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
	}