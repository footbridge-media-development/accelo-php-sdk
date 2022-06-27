<?php
	namespace FootbridgeMedia\Accelo\APIRequest;

	use GuzzleHttp\Psr7\Response;

	class RequestResponse{
		public int $httpStatus;
		public Response $response;
		public string $responseBody;
		public string $apiStatus;
		public string $apiMessage;
		public string $apiMoreInfo;
		public bool $hasMorePages;
		public RequestType $requestType;

		// Results
		private array $listRequestResult;

		/**
		 * When the rate limit will reset back to the max
		 * @var int
		 */
		public int $rateLimitResetTimestamp;

		/**
		 * How many more requests can be used before being rate limited.
		 * @var int
		 */
		public int $rateLimitRemaining;

		/**
		 * Never changes. This is what Accelo tells us is the normal maximum rate limit to expect to be able to use
		 * if no API requests had been made.
		 * @var int
		 */
		public int $rateLimitTotalMaxAllowed;

		public function setListRequest(array $objects): void{
			$this->listRequestResult = $objects;
		}

		public function getListResult(): array{
			return $this->listRequestResult;
		}
	}