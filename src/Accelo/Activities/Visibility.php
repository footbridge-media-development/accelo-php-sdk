<?php
	namespace FootbridgeMedia\Accelo\Activities;

	enum Visibility: string
	{
		case Private = "private";
		case Confidential = "confidential";
		case All = "all";
	}