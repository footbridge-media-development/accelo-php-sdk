<?php
	namespace FootbridgeMedia\Accelo\APIRequest;

	enum RequestType{
		case GET_AUTHORIZATION_URL;
		case GET_TOKENS_FROM_ACCESS_CODE;
		case LIST;
		case UPDATE;
		case CREATE;
		case RUN_PROGRESSION;
	}