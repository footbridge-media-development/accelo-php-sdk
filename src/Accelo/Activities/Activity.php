<?php

	namespace FootbridgeMedia\Accelo\Activities;

	use FootbridgeMedia\Accelo\BaseObject;
	use FootbridgeMedia\Accelo\Staff\Staff;

	class Activity extends BaseObject {

		public int $id;
		public string $subject;
		public int $confidential;
		public ?int $parent_id = null;
		public ?string $thread = null;
		public ?string $parent = null;
		public ?int $thread_id = null;
		public ?string $against_type = null;
		public ?int $against_id = null;
		public ?string $against = null;
		public ?string $owner_type = null;
		public ?int $owner_id = null;
		public ?string $owner = null;
		public ?string $medium = null;
		public ?string $body = null;
		public ?string $preview_body = null;
		public ?string $html_body = null;
		public ?string $visibility = null;
		public ?string $details = null;
		public ?int $date_created = null;
		public ?int $date_started = null;
		public ?int $date_ended = null;
		public ?int $date_logged = null;
		public ?int $date_modified = null;
		public ?int $billable = null;
		public ?int $nonbillable = null;
		public int|Staff|null $staff = null;
		/** @deprecated  */
		public int|ActivityClass|null $class = null;
		public int|ActivityClass|null $activity_class = null;
		/** @deprecated  */
		public int|ActivityPriority|null $priority = null;
		public int|ActivityPriority|null $activity_priority = null;
		public int|Task|null $task = null;
		public int|TimeAllocation|null $time_allocation = null;
		public int|Rate|null $rate = null;
		public ?int $rate_charged = null;
		public ?array $tag = null;
		public ?string $scheduled = null;
		public ?string $standing = null;
		public ?int $invoice_id = null;
		public ?int $contract_period_id = null;
		public ?int $is_billable = null;
		public ?array $permissions = null;
	}