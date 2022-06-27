<?php

	namespace FootbridgeMedia\Accelo\Address;

	class State{
		public int $id;
		public string $title;
		public string $abbreviation;
		public ?string $country_id = null;
		public ?string $ordering = null;
		public ?string $timezone = null;
	}