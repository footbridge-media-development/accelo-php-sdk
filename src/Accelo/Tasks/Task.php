<?php

	namespace FootbridgeMedia\Accelo\Tasks;

	use FootbridgeMedia\Accelo\Affiliation\Affiliation;
	use FootbridgeMedia\Accelo\BaseObject;
	use FootbridgeMedia\Accelo\Companies\Company;
	use FootbridgeMedia\Accelo\Contacts\Contact;
	use FootbridgeMedia\Accelo\Staff\Staff;
	use FootbridgeMedia\Accelo\Statuses\Status;

	class Task extends BaseObject {

		public int $id;
		public string $title;
		public ?string $description = null;
		public ?int $billable = null;
		public ?int $nonbillable = null;
		public ?int $logged = null;
		public ?int $budgeted = null;
		public ?int $remaining = null;
		public ?int $staff_bookmarked = null;
		public ?int $date_created = null;
		public ?int $date_started = null;
		public ?int $date_commenced = null;
		public ?int $date_accepted = null;
		public ?int $date_due = null;
		public ?int $date_completed = null;
		public ?int $date_modified = null;
		public ?string $against_type = null;
		public ?int $against_id = null;
		public ?string $against = null;
		public ?string $creator_type = null;
		public ?int $creator_id = null;
		public ?string $creator = null;
		public int|Staff|null $assignee = null;
		/** @deprecated */
		public int|TaskType|null $type = null;
		public int|TaskType|null $task_type = null;
		/** @deprecated */
		public int|Status|null $status = null;
		public int|Status|null $task_status = null;
		public ?string $standing = null;
		public int|Staff|null $manager = null;
		public int|Contact|null $contact = null;
		public int|Affiliation|null $affiliation = null;
		public int|Company|null $company = null;
		public int|Issue|null $issue = null;
		public ?int $rate_id = null;
		public ?float $rate_charged = null;
		public ?int $ordering = null;
	}