<?php
	namespace FootbridgeMedia\Accelo\Resources;

	class Resource{
		public int $id;
		public string $title;
		public ?int $date_created = null;
		public ?string $mimetype = null;
		public ?int $filesize = null;
		public ?int $collection_id = null;
		public ?string $owner_type = null;
		public ?int $owner_id = null;
	}