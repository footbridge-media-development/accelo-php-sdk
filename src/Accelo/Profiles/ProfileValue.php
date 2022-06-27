<?php

	namespace FootbridgeMedia\Accelo\Profiles;

	use FootbridgeMedia\Accelo\Staff\Staff;

	class ProfileValue{
		public int $id;
		public string $field_type;
		public string $field_name;
		public string $value_type;
		public mixed $value;
		public ?array $values = null;
		public ?int $date_modified = null;
		public int|Staff|null $modified_by = null;
		public ?int $field_id = null;
		public ?int $link_id = null;
	}