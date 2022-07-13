<?php

	namespace FootbridgeMedia\Accelo\Contacts;

	use FootbridgeMedia\Accelo\BaseObject;
	use FootbridgeMedia\Accelo\Statuses\Status;

	class Contact extends BaseObject {
		public int $id;
		public string $firstname;
		public string $surname;
		public string $mobile;
		public string $email;
		public ?string $username = null;
		public ?string $middlename = null;
		public ?string $title = null;
		public ?string $timezone = null;
		public ?int $date_created = null;
		public ?int $date_modified = null;
		public ?int $date_last_interacted = null;
		public ?string $comments = null;
		public ?int $default_affiliation = null;
		public int|Status|null $status = null;
		public ?string $standing = null;
	}