<?php
require_once __DIR__ . "/../vendor/autoload.php";

use FootbridgeMedia\Accelo\Accelo;
use FootbridgeMedia\Accelo\APIRequest\RequestSender;
use FootbridgeMedia\Accelo\Authentication\AuthenticationType;
use FootbridgeMedia\Accelo\Authentication\WebAuthentication;
use FootbridgeMedia\Accelo\ClientCredentials\ClientCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class DeleteResourceTest extends TestCase
{
	public function testDelete(){
		$mock = new MockHandler([
			new Response(
				200,
				[
					"X-RateLimit-Reset" => time() + 3600,
					"X-RateLimit-Remaining" => 4999,
					"X-RateLimit-Limit" => 5000,
				],
				json_encode([
					"meta" => [
						"more_info" => "https://affinitylive.jira.com/wiki/display/APIS/Status+Codes#ok",
						"status" => "ok",
						"message" => "Everything executed as expected.",
					],
				])
			),
		]);
		$client = new Client(["handler" => HandlerStack::create($mock)]);
		$requestSender = new RequestSender($client);
		$accelo = new Accelo($requestSender);

		$authentication = new WebAuthentication();
		$authentication->accessToken = bin2hex(random_bytes(16));
		$authentication->refreshToken = bin2hex(random_bytes(16));
		$authentication->authType = AuthenticationType::Bearer;
		$accelo->setAuthentication($authentication);

		$clientCredentials = new ClientCredentials();
		$clientCredentials->deploymentName = "testing";
		$clientCredentials->clientID = bin2hex(random_bytes(16));
		$clientCredentials->clientSecret = bin2hex(random_bytes(16));
		$accelo->setCredentials($clientCredentials);

		$response = $accelo->delete(
			endpoint: "/activities/1"
		);
		$this->assertEquals('ok', $response->apiStatus);
	}
}
