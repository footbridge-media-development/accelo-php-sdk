<?php
	namespace FootbridgeMedia\Accelo\Requests;

	use FootbridgeMedia\Accelo\Affiliation\Affiliation;
	use FootbridgeMedia\Accelo\BaseObject;
	use FootbridgeMedia\Accelo\Staff\Staff;

	class Request extends BaseObject {
		public int $id;
		public string $title;
		public ?string $body = null;
		public ?string $standing = null;
		public ?string $source = null;
		public ?int $lead_id = null;
		public ?int $thread_id = null;
		public ?int $date_created = null;
		public ?int $date_modified = null;
		public ?int $type_id = null;
		public int|RequestType|null $type = null;
		public ?int $priority_id = null;
		public int|RequestPriority|null $priority = null;
		public ?int $claimer_id = null;
		public int|Staff|null $claimer = null;
		public ?int $affiliation_id = null;
		public int|Affiliation|null $affiliation = null;

	}