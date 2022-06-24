<?php

	namespace FootbridgeMedia\Accelo\Asset;

	class AssetLink{
		public ?string $asset_id = null;
		public ?string $linked_object_id = null;
		public ?string $linked_object_title = null;
		public ?string $linked_object_type = null;
		public ?string $asset_link_id = null;
		public ?array $linked_object_breadcrumbs = null;
		public ?string $description = null;
		public ?string $start_date = null;
		public ?string $end_date = null;
	}