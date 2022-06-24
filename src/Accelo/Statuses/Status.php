<?php

	namespace FootbridgeMedia\Accelo\Statuses;

	class Status{

		public int $id;
		public string $title;
		public ?string $color = null;
		public ?string $standing = null;
		public ?string $start = null;
		public ?int $ordering = null;
	}