<?php
	namespace FootbridgeMedia\Accelo\APIRequest\RequestConfigurations;

	class AdditionalFields{
		private array $fieldNames = [];

		public function addField(
			string $fieldName,
			string | null $fieldValue = null,
		): void{
			$formattedFieldString = $fieldName;

			// Handle when a field value is provided.
			// This means it must be provided in parentheses after the name
			if ($fieldValue !== null){
				$formattedFieldString .= sprintf("(%s)", $fieldValue);
			}

			$this->fieldNames[] = $formattedFieldString;
		}

		public function getFieldsAsCommaSeparatedList(): string{
			return implode(",",$this->fieldNames);
		}
	}