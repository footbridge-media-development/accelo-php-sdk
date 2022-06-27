<?php

	namespace FootbridgeMedia\Accelo\Activities;

	class TimeAllocation{
		public int $id;
		public ?string $against = null;
		public ?string $standing = null;
		public ?int $billable = null;
		public ?int $nonbillable = null;
		public ?float $charged = null;
		public ?string $comments = null;
		public ?int $date_locked = null;
		public ?int $date_created = null;
	}