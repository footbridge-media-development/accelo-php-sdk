<?php

	namespace FootbridgeMedia\Accelo\Activities;

	class ActivityClass{

		public int $id;
		public string $title;
		public ?string $parent = null;
		public ?string $status = null;
	}