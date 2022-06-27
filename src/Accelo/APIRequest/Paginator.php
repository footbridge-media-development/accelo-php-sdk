<?php
	namespace FootbridgeMedia\Accelo\APIRequest;

	/**
	 * Pagintor class to help tell the RequestSender class how to paginate the Accelo API request.
	 * Pages should start at 0. The maximum API request limit is 100.
	 */
	class Paginator{

		/**
		 * The default return limit for Accelo API requests
		 */
		const DEFAULT_LIMIT = 10;

		private int $page = 0;
		private int $limit = self::DEFAULT_LIMIT;

		public function setPage(int $page): void{
			$this->page = $page;
		}

		/**
		 * @throws ValueOutOfRange
		 */
		public function setLimit(int $limit): void{
			if ($limit > 100){
				throw new ValueOutOfRange("The Accelo API does not accept limits above 100.");
			}
			$this->limit = $limit;
		}

		public function getPage(): int{
			return $this->page;
		}

		public function getLimit(): int{
			return $this->limit;
		}

		public function incrementPage(): void{
			$this->page += 1;
		}
	}