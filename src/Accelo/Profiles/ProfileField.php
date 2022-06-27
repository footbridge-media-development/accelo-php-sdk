<?php

	namespace FootbridgeMedia\Accelo\Profiles;

	class ProfileField{
		public int $id;
		public string $field_name;
		public string $field_type;
		public ?int $parent_id = null;
		public ?string $link_type;
		public ?string $required = null;
		public ?string $restrictions = null;
		public ?string $exported = null;
		public ?string $lookup_type = null;
		public ?array $options = null;
		public ?string $confidential = null;
		public ?string $description = null;
	}