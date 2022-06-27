<?php
	namespace FootbridgeMedia\Accelo\Staff;

	class Staff{
		public int $id;
		public string $firstname;
		public string $surname;
		public ?string $standing = null;
		public ?string $financial_level = null;
		public ?string $title = null;
		public ?string $email = null;
		public ?string $mobile = null;
		public ?string $phone = null;
		public ?string $fax = null;
		public ?string $position = null;
		public ?string $username = null;
		public ?string $timezone = null;
	}