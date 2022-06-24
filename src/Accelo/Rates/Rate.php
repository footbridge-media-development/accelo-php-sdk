<?php

	namespace FootbridgeMedia\Accelo\Rates;

	class Rate{

		public int $id;
		public string $title;
		public ?float $charged = null;
		public ?string $standing = null;
		public ?string $object = null;
	}