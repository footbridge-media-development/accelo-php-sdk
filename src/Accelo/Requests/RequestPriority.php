<?php
	namespace FootbridgeMedia\Accelo\Requests;

	class RequestPriority{
		public int $id;
		public string $title;
		public ?string $color = null;
		public ?int $factor = null;
	}