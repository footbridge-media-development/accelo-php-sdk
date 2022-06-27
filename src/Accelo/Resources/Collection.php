<?php
	namespace FootbridgeMedia\Accelo\Resources;

	class Collection{
		public int $id;
		public string $title;
		public ?string $against_type = null;
		public ?int $against_id = null;
	}