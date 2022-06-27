<?php
	namespace FootbridgeMedia\Accelo\Activities;

	enum Medium: string
	{
		case Note = "note";
		case Email = "email";
		case Meeting = "meeting";
		case Call = "call";
		case Postal = "postal";
	}