<?php

	namespace FootbridgeMedia\Accelo\Companies;

	use FootbridgeMedia\Accelo\Address\Address;
	use FootbridgeMedia\Accelo\BaseObject;
	use FootbridgeMedia\Accelo\Statuses\Status;

	class Company extends BaseObject {
		public int $id;
		public string $name;
		public ?string $custom_id = null;
		public ?string $website = null;
		public ?string $phone = null;
		public ?string $fax = null;
		public ?int $date_created = null;
		public ?int $date_modified = null;
		public ?int $date_last_interacted = null;
		public ?string $comments = null;
		public ?string $standing = null;
		public int|Status|null $status = null;
		public int|Address|null $postal_address = null;
		public ?int $staff_bookmarked = null;
		public ?int $default_affiliation = null;
	}