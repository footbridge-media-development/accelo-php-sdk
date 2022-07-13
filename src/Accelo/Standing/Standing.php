<?php
	namespace FootbridgeMedia\Accelo\Standing;

	enum Standing: string{
		case SUBMITTED = "submitted";
		case OPEN = "open";
		case RESOLVED = "resolved";
		case CLOSED = "closed";
		case INACTIVE = "inactive";
	}