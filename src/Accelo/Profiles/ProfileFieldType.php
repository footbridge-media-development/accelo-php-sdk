<?php
	namespace FootbridgeMedia\Accelo\Profiles;

	enum ProfileFieldType: string{
		case TEXT = "text";
		case INTEGER = "integer";
		case DECIMAL = "decimal";
		case DATE = "date";
		case DATE_TIME = "date_time";
		case CURRENCY = "currency";
		case LOOKUP = "lookup";
		case STRUCTURE = "structure";
		case SELECT = "select";
		case MULTI_SELECT = "multi_select";
		case HYPERLINK = "hyperlink";
	}