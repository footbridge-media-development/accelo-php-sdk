<?php

	namespace FootbridgeMedia\Accelo\Issues;

	use FootbridgeMedia\Accelo\Affiliation\Affiliation;
	use FootbridgeMedia\Accelo\BaseObject;
	use FootbridgeMedia\Accelo\Companies\Company;
	use FootbridgeMedia\Accelo\Staff\Staff;
	use FootbridgeMedia\Accelo\Statuses\Status;
	use FootbridgeMedia\Resources\Attributes\Deprecated;

	class Issue extends BaseObject {
		public int $id;
		public string $title;
		public ?string $custom_id = null;
		public ?string $description = null;
		public ?string $against = null;
		public ?string $against_type = null;
		public ?int $against_id = null;
		/** @deprecated */
		#[Deprecated]
		public int|IssueType|null $type = null;
		public int|IssueType|null $issue_type = null;
		public int|Affiliation|null $affiliation = null;
		public int|IssueClass|null $class = null;
		/** @deprecated */
		#[Deprecated]
		public int|IssuePriority|null $priority = null;
		public int|IssuePriority|null $issue_priority = null;
		public int|IssueResolution|null $resolution = null;
		public ?string $resolution_detail = null;
		public int|Status|null $status = null;
		public ?string $standing = null;
		public ?int $date_submitted = null;
		public int|Staff|null $submitted_by = null;
		public ?int $date_opened = null;
		public int|Staff|null $opened_by = null;
		public ?int $date_resolved = null;
		public int|Staff|null $resolved_by = null;
		public ?int $date_closed = null;
		public int|Staff|null $closed_by = null;
		public ?int $date_due = null;
		public ?int $date_last_interacted = null;
		public ?int $date_modified = null;
		public ?string $referrer_type = null;
		public ?int $referrer_id = null;
		public ?int $staff_bookmarked = null;
		public ?int $billable_seconds = null;
		public int|Company|null $company = null;
		public int|Staff|null $assignee = null;
	}