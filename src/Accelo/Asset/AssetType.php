<?php

	namespace FootbridgeMedia\Accelo\Asset;

	class AssetType{
		public int $id;
		public ?string $title = null;
		public ?string $object_link_fields = null;
		public ?string $standing = null;
		public ?string $has_manager = null;
		public ?string $has_affiliation = null;
		public ?string $has_address = null;
		public ?string $ordering = null;
	}