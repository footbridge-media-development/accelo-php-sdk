<?php

	namespace FootbridgeMedia\Accelo\Asset;

	use FootbridgeMedia\Accelo\Address\Address;
	use FootbridgeMedia\Accelo\Affiliation\Affiliation;

	class Asset{
		public int $id;
		public string $title;
		public string $latest_asset_link;
		public ?string $standing = null;
		public ?int $date_created = null;
		public ?string $against_type = null;
		public ?int $against_id = null;
		public int|AssetType|null $asset_type = null;
		public ?int $asset_type_id = null;
		public int|Affiliation|null $affiliation = null;
		public ?int $affiliation_id = null;
		public int|User|null $manager = null;
		public ?int $manager_id = null;
		public string|Address|null $address = null;
		public ?int $address_id = null;
	}