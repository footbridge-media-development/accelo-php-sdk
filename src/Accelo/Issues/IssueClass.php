<?php

	namespace FootbridgeMedia\Accelo\Issues;

	class IssueClass{
		public int $id;
		public string $title;
		public ?string $description = null;
		public ?int $parent = null;
		public ?string $standing = null;
	}