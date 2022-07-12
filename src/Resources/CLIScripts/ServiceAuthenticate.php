<?php

	require_once getcwd() . "/vendor/autoload.php";

	use FootbridgeMedia\Accelo\Accelo;
	use FootbridgeMedia\Accelo\ClientCredentials\ClientCredentials;
	use FootbridgeMedia\Resources\Exceptions\APIException;

	class ServiceAuthenticate{

		const SCOPE = "read(all),write(all)";
		const DEFAULT_EXPIRES_IN_SECONDS = 86400 * (365 * 3); // 3 years - max time

		/**
		 * @throws Exception
		 */
		public function __construct(){
			if (strtolower(php_sapi_name()) !== "cli"){
				throw new Exception("Can only run ServiceAuthenticate from the command line.");
			}

			$this->performServiceAuthorization();
		}

		public function performServiceAuthorization(): void{
			print("Enter your Accelo deployment name and press enter: ");
			$deploymentName = trim(fgets(STDIN));
			print("Enter your Accelo web client ID and press enter: ");
			$clientID = trim(fgets(STDIN));
			print("Enter your Accelo web client secret key and press enter: ");
			$clientSecret = trim(fgets(STDIN));

			$clientCredentials = new ClientCredentials();
			$clientCredentials->deploymentName = $deploymentName;
			$clientCredentials->clientID = $clientID;
			$clientCredentials->clientSecret = $clientSecret;

			$accelo = new Accelo();
			$accelo->setCredentials($clientCredentials);

			try {
				$tokensArray = $accelo->getTokensForServiceApplication(
					scope: self::SCOPE,
					expiresInSeconds:self::DEFAULT_EXPIRES_IN_SECONDS,
				);
			}catch(APIException $e){
				printf("Accelo responded with an error: (%s): %s", $e->apiStatus, $e->apiErrorMessage);
				exit();
			}


			print("oAuth authorization success. Your access codes and expiratory information is below. Please keep it safe in the dark:\n");
			print_r([
				"access_token"=>$tokensArray['access_token'],
				"refresh_token"=>$tokensArray['refresh_token'],
				"expires_in"=>$tokensArray['expires_in'],
				"expires_on"=>date("M d, Y", time() + $tokensArray['expires_in']),
				"deployment_uri"=>$tokensArray['deployment_uri'],
				"token_type"=>$tokensArray['token_type'],
			]);
		}
	}

	new ServiceAuthenticate();