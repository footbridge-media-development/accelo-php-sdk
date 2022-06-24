<?php

	namespace FootbridgeMedia\Accelo\Issues;

	class IssueResolution{
		public int $id;
		public string $title;
		public ?int $parent = null;
		public ?string $description = null;
		public ?string $report = null;
		public ?string $standing = null;
	}