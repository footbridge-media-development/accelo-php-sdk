<?php

	namespace FootbridgeMedia\Accelo\Address;

	use FootbridgeMedia\Accelo\BaseObject;

	class Address extends BaseObject {
		public int $id;
		public string $full;
		public ?string $title = null;
		public ?string $street1 = null;
		public ?string $street2 = null;
		public ?string $city = null;
		public ?string $zipcode = null;
		public ?string $state = null;
		public ?string $country = null;
		public ?string $postal = null;
		public ?string $physical = null;
	}