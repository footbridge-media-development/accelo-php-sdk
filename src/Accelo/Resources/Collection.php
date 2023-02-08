<?php
	namespace FootbridgeMedia\Accelo\Resources;

	use FootbridgeMedia\Accelo\BaseObject;

	class Collection extends BaseObject {
		public int $id;
		public string $title;
		public ?string $against_type = null;
		public ?int $against_id = null;
	}