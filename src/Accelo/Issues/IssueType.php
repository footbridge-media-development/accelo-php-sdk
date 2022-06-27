<?php

	namespace FootbridgeMedia\Accelo\Issues;

	class IssueType{
		public int $id;
		public string $title;
		public ?string $notes = null;
		public ?int $parent = null;
		public ?string $standing = null;
		public ?string $budget = null;
		public ?int $ordering = null;
	}