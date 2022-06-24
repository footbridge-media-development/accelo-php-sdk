<?php
	namespace FootbridgeMedia\Accelo\Staff;

	class StaffMembership{
		public int $id;
		public ?int $group_id = null;
		public ?int $staff_id = null;
		public int|Staff|null $staff = null;
	}