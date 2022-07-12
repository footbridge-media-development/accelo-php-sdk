# Accelo PHP SDK
An Unofficial, PHP 8.1+ Accelo PHP SDK created by Footbridge Media developer Garet C. Green.

## Requirements
- Guzzle 7.4 and above
- PHP 8.1 and above

## Usage
First, you must setup authentication and credentials before requests will be successfully processed.

Install using composer
```
composer require footbridge-media/accelo-php-sdk
```

### Authenticate Your Accelo Account via oAuth for Web Application Use
This library comes with a built-in authenticator for CLI use that will allow you to authenticate your Accelo staff account with the oAuth2 API they have.

Make sure to have your Deployment Name, Client ID, and Client Secret handy and then run the following script via the CLI.
```
php vendor/footbridge-media/accelo-php-sdk/src/Resources/CLIScripts/WebAuthenticate.php
```

Follow the prompts and provide the information they request until it outputs a URL. 

```
Enter your Accelo deployment name and press enter:
Enter your Accelo web client ID and press enter:
Enter your Accelo web client secret key and press enter:
Visit this URL in your browser to authorize this library to use your Accelo account for the API - URL_WILL_BE_HERE
```

Copy the full URL and visit it in a web browser.

Sign in to your Accelo account by following the link, and it will give you a numerical access code. Return to the CLI prompt where it asks you for this access code.

```
After allowing access, enter the access code you were given here and press enter: 
```

You will then be given the necessary access_token and refresh_token to use with this library and the Accelo API.

```
oAuth authorization succeeding. Your access codes and expiratory information is below. Please keep it safe in the dark:
Array                                                                                 
(                                                                                     
    [access_token] => TOKEN_HERE
    [refresh_token] => TOKEN_HERE
    [expires_in] => SECONDS_UNTIL_EXPIRES
    [expires_on] => DATE_OF_EXPIRATORY
    [deployment_uri] => DEPLOYMENT_URL
    [token_type] => TOKEN_TYPE
)
```

Save your two tokens (access and refresh) for use in this library below. Usually you would store them in a non-committed environment file.

You may also wish to store the expires_in and expires_on information so you know when you need to renew your tokens. You can now continue to utilizing the rest of this library below.

### Authenticating a Service Application
If you wish you use a service application instead of authenticating a web user, the process is similar to the above authentication section.

Make sure to have your Deployment Name, Client ID, and Client Secret handy and then run the following script via the CLI. Run this script from your application's root.
```
php vendor/footbridge-media/accelo-php-sdk/src/Resources/CLIScripts/ServiceAuthenticate.php
```

Follow the prompts and provide the information they request.

```
Enter your Accelo deployment name and press enter:
Enter your Accelo web client ID and press enter:
Enter your Accelo web client secret key and press enter:
```

If all the information is correct, then Accelo's oAuth API will authorize your application for 3 years. You will then be given the necessary access_token to use with this library and the Accelo API.

```
oAuth authorization succeeding. Your access codes and expiratory information is below. Please keep it safe in the dark:
Array                                                                                 
(                                                                                     
    [access_token] => TOKEN_HERE
    [refresh_token] => 
    [expires_in] => SECONDS_UNTIL_EXPIRES
    [expires_on] => DATE_OF_EXPIRATORY
    [deployment_uri] => DEPLOYMENT_URL
    [token_type] => TOKEN_TYPE
)
```

You will only need to use the access_token for a service application.

You may also wish to store the expires_in and expires_on information so you know when you need to renew your tokens. You can now continue to utilizing the rest of this library below.

### Initiate a New Accelo Object
```php
use FootbridgeMedia\Accelo\Accelo;

$accelo = new Accelo();
```

This object will be given an authentication object and a credentials object. Then it will be used to call the API.

### Client Authentication Object
Currently, only the web authentication is supported. However, there is the framework already paved for later implementations using a service authentication method.
```php
use FootbridgeMedia\Accelo\Authentication\AuthenticationType;
use FootbridgeMedia\Accelo\Authentication\WebAuthentication;
use FootbridgeMedia\Accelo\Authentication\ServiceAuthentication;

// For web authentication
$authentication = new WebAuthentication();
$authentication->authType = AuthenticationType::Bearer;
$authentication->accessToken = "USER_ACCESS_TOKEN";
$authentication->refreshToken = "USER_REFRESH_TOKEN";

// For service authentication, use the below INSTEAD of the above
$authentication = new ServiceAuthentication();
$authentication->authType = AuthenticationType::Bearer;
$authentication->accessToken = "SERVICE_ACCESS_TOKEN";
```

### Client Credentials Object
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
$accelo->setAuthentication($authentication);
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

#### Paginating Results
Accelo's maximum limit on returned results is 100 entries of any object. The default is 10. You can specify which page and what limit you want the list calls to return with a Paginator object. Shown below. We request all companies to be returned with a limit of 15 each API call. Additionally, there is a filter to order them by name in ascending order.
```php
// ORDER BY filter
$filters = new Filters();
$filters->addFilter(
    filterName:"order_by_asc",
    filterValue: "name",
);

$paginator = new Paginator();
$paginator->setLimit(15);
$paginator->setPage(0); // Page 0 is the starting page

// Perform the request
$requestResponse = self::$accelo->list(
    endpoint: "/companies",
    objectType: Company::class,
    filters: $filters,
    paginator: $paginator,
);

/** @var Company[] $companies */
$companies = $requestResponse->getListResult();

foreach($companies as $company){
    printf("{$company->name}\n");
}

if ($requestResponse->hasMorePages){
    // Increment the paginator
    $paginator->incrementPage();
    
    // Repeat the same request
    $requestResponseNextPage = self::$accelo->list(
        endpoint: "/companies",
        objectType: Company::class,
        filters: $filters,
        paginator: $paginator,
    );
    
    /** @var Company[] $companies */
    $companiesFromNextPage = $requestResponseNextPage->getListResult();
}
```

As you can see at the bottom, you can use the hasMorePages boolean flag to tell if a response probably has more results to return. Then you would increment the paginator with incrementPage. You can then make the same API call with the same object filters, fields, and same paginator instance but you will receive new results.
