<?php

	namespace FootbridgeMedia\Accelo\Authentication;

	class WebAuthentication extends Authentication {

		public AuthenticationType $authType;
		public string $accessToken;
		public string $refreshToken;

		/**
		 * Performs the full user authentication process for a user's email. Must be called from the CLI.
		 * @param string $userEmail
		 * @return WebAuthentication
		 * @throws Exception
		 */
		public static function performUserAuthentication(string $userEmail): WebAuthentication{
			if (PHP_SAPI !== "cli"){
				throw new Exception("This function can only be called from the command line.");
			}

			$apiAuth = new WebAuthentication();
			$postFields = [
				"client_id"=>\NoxEnv::ACCELO_WEB_CLIENT_ID,
				"response_type"=>"code",
				"scope"=>"read(all),write(all)",
			];
			$apiRequest = new APIRequest(
				host: APIRequest::OAUTH2_HOST,
				method:"post",
				endpoint: "/authorize",
				headers: [],
				authorization: $apiAuth
			);
			$apiRequest->urlEncodedPostFields = $postFields;
			$apiAuth->authType = WebAuthentication::AUTH_TYPES['None'];
			$apiResponse = $apiRequest->call();

			if ($apiResponse->statusCode === 302){
				// This is fine
				$authURL = $apiResponse->headers['location'];
				printf("Visit this URL and enter the pin you receive below. URL to visit: %s\n", $authURL);
				print("Enter the code here: ");
				$cliInput = fopen("php://stdin", "r");
				$pin = trim(fgets($cliInput));

				// Authorize this pin
				$postFieldsPin = [
					"client_id"=>\NoxEnv::ACCELO_WEB_CLIENT_ID,
					"grant_type"=>"authorization_code",
					"code"=>$pin,
					"expires_in"=>86400 * 365, // Expire in 1 year
				];
				$apiAuthPin = new WebAuthentication();
				$apiAuthPin->authType = WebAuthentication::AUTH_TYPES['Basic'];
				$apiAuthPin->clientID = \NoxEnv::ACCELO_WEB_CLIENT_ID;
				$apiAuthPin->clientSecret = \NoxEnv::ACCELO_WEB_CLIENT_SECRET;
				$apiRequestPin = new APIRequest(
					host: APIRequest::OAUTH2_HOST,
					method:"post",
					endpoint: "/token",
					headers: [
						"accepts"=>"application/json",
					],
					authorization: $apiAuthPin
				);
				$apiRequestPin->urlEncodedPostFields = $postFieldsPin;
				$apiResponsePin = $apiRequestPin->call();
				if ($apiResponsePin->statusCode === 200){
					printf("Authentication user successfully.\n");
					$userDataFromAPI = json_decode($apiResponsePin->body, true);
					$accessToken = $userDataFromAPI['access_token'];
					$refreshToken = $userDataFromAPI['refresh_token'];
					$newUserAuth = new WebAuthentication();
					$newUserAuth->authType = WebAuthentication::AUTH_TYPES['Bearer'];
					$newUserAuth->userAccessToken = $accessToken;
					$newUserAuth->userRefreshToken = $refreshToken;
					$newUserAuth->logAuthForUser($userEmail, (int) $userDataFromAPI['expires_in']);
					return $newUserAuth;
				}else{
					printf("Error: %s\n", $apiResponsePin->body);
				}
			}
		}
	}