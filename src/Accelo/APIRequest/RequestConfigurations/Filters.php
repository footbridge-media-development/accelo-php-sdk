<?php
	namespace FootbridgeMedia\Accelo\APIRequest\RequestConfigurations;

	class Filters{
		private array $filters = [];

		public function addFilter(
			string $filterName,
			string | null $filterValue = null,
		): void{
			$formattedFilterString = $filterName;

			// Handle when a filter value is provided.
			// This means it must be provided in parentheses after the name
			if ($filterValue !== null){
				$formattedFilterString .= sprintf("(%s)", $filterValue);
			}

			$this->filters[] = $formattedFilterString;
		}

		public function getFiltersAsCommaSeparatedString(): string{
			return implode(",",$this->filters);
		}
	}