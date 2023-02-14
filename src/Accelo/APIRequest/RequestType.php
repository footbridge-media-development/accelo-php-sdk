<?php
	namespace FootbridgeMedia\Accelo\APIRequest;

	enum RequestType{
		case GET_AUTHORIZATION_URL;
		case GET_TOKENS_FROM_ACCESS_CODE;
		case GET_OBJECT;
		case LIST;
		case UPDATE;
		case CREATE;
		case DELETE;
		case UPLOAD_RESOURCE;
		case RUN_PROGRESSION;
	}