<?php

	namespace FootbridgeMedia\Accelo\Issues;

	class IssueStatus{
		public int $id;
		public string $title;
		public ?string $standing = null;
		public ?string $color = null;
		public ?string $start = null;
		public ?int $ordering = null;
	}