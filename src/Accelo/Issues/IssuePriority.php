<?php

	namespace FootbridgeMedia\Accelo\Issues;

	class IssuePriority{
		public int $id;
		public string $title;
		public ?string $color = null;
		public ?int $factor = null;
	}