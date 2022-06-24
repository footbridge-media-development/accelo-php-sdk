<?php

	namespace FootbridgeMedia\Accelo\Address;

	class Country{
		public int $id;
		public string $title;
		public ?string $prefix = null;
		public ?string $suffix = null;
		public ?string $postcode_name = null;
		public ?string $state_name = null;
		public ?string $state_required = null;
		public ?string $postcode_required = null;
	}