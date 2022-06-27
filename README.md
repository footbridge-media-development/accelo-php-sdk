# Accelo PHP SDK
An Unofficial, PHP 8.1+ Accelo PHP SDK created by Footbridge Media developer Garet C. Green.

## Requirements
- Guzzle 7.4 and above
- PHP 8.1 and above

## Usage
First, you must setup authentication and credentials before requests will be successfully processed.

Install using composer
```
composer require footbridge-media-development/accelo-php-sdk
```

### Initiate a New Accelo Object
```php
use FootbridgeMedia\Accelo\Accelo;

$accelo = new Accelo();
```

This object will be given an authentication object and a credentials object. Then it will be used to call the API.

### Client Authentication
Currently, only the web authentication is supported. However, there is the framework already paved for later implementations using a service authentication method.
```php
use FootbridgeMedia\Accelo\Authentication\AuthenticationType;
use FootbridgeMedia\Accelo\Authentication\WebAuthentication;
	
$webAuthentication = new WebAuthentication();
$webAuthentication->authType = AuthenticationType::Bearer;
$webAuthentication->accessToken = "USER_ACCESS_TOKEN";
$webAuthentication->refreshToken = "USER_REFRESH_TOKEN";
```

### Client Credentials
```php
use FootbridgeMedia\Accelo\ClientCredentials\ClientCredentials;

$clientCredentials = new ClientCredentials();
$clientCredentials->deploymentName = "DEPLOYMENT_NAME";
$clientCredentials->clientID = "CLIENT_ID";
$clientCredentials->clientSecret = "CLIENT_SECRET";
```

### Registering Authentication and Credentials
Now, register both the authentication and credentials instances with the Accelo object.

```php
$accelo->setAuthentication($webAuthentication);
$accelo->setCredentials($clientCredentials);
```

### API Call Types
As of this README and library version, the `list` type of API calls is implemented with all available filters, searches, and extra field parameters supported. 

#### Listing Companies
The following is an example of listing companies with a provided filter and search.
```php
use FootbridgeMedia\Accelo\Companies\Company;

// Set up a search query for the company results
$search = new Search();
$search->setQuery("Footbridge Media");

// Setup filters
$filters = new Filters();
$filters->addFilter(
    filterName:"standing",
    filterValue: "active",
);

// Perform the request
try{
    $requestResponse = self::$accelo->list(
        endpoint: "/companies",
        objectType: Company::class,
        filters: $filters,
        search: $search,
    );
}catch (\FootbridgeMedia\Resources\Exceptions\APIException $e) {
    print($e->getMessage());
} catch (\GuzzleHttp\Exception\GuzzleException $e) {
    // HTTP error from Guzzle
    print($e->getMessage());
}

/** @var Company[] $companies */
$companies = $requestResponse->getListResult();

foreach($companies as $company){
    printf("{$company->name}\n");
}
```