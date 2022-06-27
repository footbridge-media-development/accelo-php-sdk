<?php

	require_once __DIR__ . "/../../../vendor/autoload.php";

	use FootbridgeMedia\Accelo\Accelo;
	use FootbridgeMedia\Accelo\ClientCredentials\ClientCredentials;
	use FootbridgeMedia\Resources\Exceptions\APIException;

	class WebAuthenticate{

		const SCOPE = "read(all),write(all)";
		const DEFAULT_EXPIRES_IN_SECONDS = 86400 * 365; // 1 year

		/**
		 * @throws Exception
		 */
		public function __construct(){
			if (strtolower(php_sapi_name()) !== "cli"){
				throw new Exception("Can only run WebAuthenticate from the command line.");
			}

			$this->performWebAuthorization();
		}

		public function performWebAuthorization(): void{
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
				$authorizationURI = $accelo->getAuthorizationURL(self::SCOPE);
			}catch(APIException $e){
				printf("Accelo responded with an error: (%s): %s", $e->apiStatus, $e->apiErrorMessage);
				exit();
			}

			printf(
				"Visit this URL in your browser to authorize this library to use your Accelo account for the API - %s\n",
				$authorizationURI
			);
			printf("After allowing access, enter the access code you were given here and press enter: ");
			$accessCode = trim(fgets(STDIN));

			try {
				$tokensArray = $accelo->getTokensFromAccessCode(
					accessCode: $accessCode,
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

	new WebAuthenticate();