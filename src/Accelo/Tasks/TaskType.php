<?php

	namespace FootbridgeMedia\Accelo\Tasks;

	class TaskType{
		public int $id;
		public string $title;
		public ?string $standing = null;
		public ?int $ordering = null;
	}