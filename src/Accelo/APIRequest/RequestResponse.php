<?php
	namespace FootbridgeMedia\Accelo\APIRequest;

	use FootbridgeMedia\Accelo\BaseObject;
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
		public BaseObject $fetchedObject; // From a get API call
		public BaseObject $createdObject;
		public BaseObject $updatedObject;
		public BaseObject $progressedObject;

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

		public function setUpdatedObject(BaseObject $object): void{
			$this->updatedObject = $object;
		}

		public function getUpdatedObject(): BaseObject{
			return $this->updatedObject;
		}

		public function setProgressedObject(BaseObject $object): void{
			$this->progressedObject = $object;
		}

		public function getProgressedObject(): BaseObject{
			return $this->progressedObject;
		}

		public function setCreatedObject(BaseObject $object): void{
			$this->createdObject = $object;
		}

		public function getCreatedObject(): BaseObject{
			return $this->createdObject;
		}

		public function setFetchedObject(BaseObject $object): void{
			$this->fetchedObject = $object;
		}

		public function getFetchedObject(): BaseObject{
			return $this->fetchedObject;
		}
	}