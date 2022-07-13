<?php
	namespace FootbridgeMedia\Accelo\APIRequest\RequestConfigurations;

	/**
	 * This class is used for update and create methods - not for returning additional fields from a response
	 */
	class Fields{
		private array $fieldsKeyValue = [];

		public function addField(
			string $fieldName,
			string $fieldValue,
		): void{
			$this->fieldsKeyValue[$fieldName] = $fieldValue;
		}

		public function getFieldsAsKeyValuePairs(): array{
			return $this->fieldsKeyValue;
		}
	}