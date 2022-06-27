<?php

	namespace FootbridgeMedia\Accelo\Authentication;

	enum AuthenticationType: int{
		case None = 0;
		case Basic = 1;
		case Bearer = 2;
	}