<?php
	namespace FootbridgeMedia\Accelo\APIRequest\RequestConfigurations;

	class Search{
		private string $query;

		public function setQuery(string $query): void{
			$this->query = $query;
		}

		public function getQuery(): string{
			return $this->query;
		}
	}