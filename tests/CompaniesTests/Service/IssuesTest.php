<?php
	require_once __DIR__ . "/../../../vendor/autoload.php";
	require_once __DIR__ . "/../../test-env.php";

	use FootbridgeMedia\Accelo\Accelo;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\AdditionalFields;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\Fields;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\Filters;
	use FootbridgeMedia\Accelo\APIRequest\RequestConfigurations\Search;
	use FootbridgeMedia\Accelo\Authentication\AuthenticationType;
	use FootbridgeMedia\Accelo\Authentication\ServiceAuthentication;
	use FootbridgeMedia\Accelo\ClientCredentials\ClientCredentials;
	use FootbridgeMedia\Accelo\Companies\Company;
	use FootbridgeMedia\Accelo\Issues\Issue;
	use FootbridgeMedia\Accelo\Standing\Standing;
	use PHPUnit\Framework\TestCase;

	final class IssuesTest extends TestCase{
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

		public function testProgressIssue(){

			$issueID = 30155;
			$issue = new Issue();
			$issue->id = $issueID;

			$testProgressionID = 910;
			$newStatusIDAfterProgression = 40;

			$additionalReturnFields = new AdditionalFields();
			$additionalReturnFields->addField(
				fieldName: "status",
			);

			$requestResponse = $issue->runProgression(
				accelo: self::$accelo,
				progressionID: $testProgressionID,
				additionalFields:$additionalReturnFields,
			);

			/** @var Issue $progressedIssue */
			$progressedIssue = $requestResponse->getProgressedObject();

			$this->assertEquals(
				expected: $progressedIssue->status,
				actual:$newStatusIDAfterProgression,
			);

			// Run it to the next progression - which, in this test, is Open
			$nextProgressionID = 912;
			$nextExpectedStatus = 2;
			$requestResponse = $progressedIssue->runProgression(
				accelo: self::$accelo,
				progressionID:$nextProgressionID,
				additionalFields:$additionalReturnFields,
			);

			/** @var Issue $progressedIssue */
			$progressedIssue = $requestResponse->getProgressedObject();

			$this->assertEquals(
				expected: $progressedIssue->status,
				actual:$nextExpectedStatus,
			);
		}
	}