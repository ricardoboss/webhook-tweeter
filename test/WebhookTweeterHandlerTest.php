<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTweeter;

use JsonException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Mockery as M;
use ricardoboss\WebhookTweeter\Simple\SimpleWebhookTweeterRenderer;
use ricardoboss\WebhookTweeter\Simple\SimpleWebhookTweeterTemplateLocator;
use stdClass;

/**
 * @covers \ricardoboss\WebhookTweeter\WebhookTweeterHandler
 * @covers \ricardoboss\WebhookTweeter\Simple\SimpleWebhookTweeterRenderer
 * @covers \ricardoboss\WebhookTweeter\Simple\SimpleWebhookTweeterTemplateLocator
 * @covers \ricardoboss\WebhookTweeter\Simple\SimpleWebhookTweeterTemplate
 * @covers \ricardoboss\WebhookTweeter\WebhookTweeterResult
 *
 * @internal
 */
class WebhookTweeterHandlerTest extends TestCase
{
	/**
	 * @throws JsonException
	 */
	public function requestProvider(): iterable
	{
		$factory = new Psr17Factory();

		$config = new WebhookTweeterConfig('/webhook', 'secret');
		$renderer = new SimpleWebhookTweeterRenderer();
		$templateLocator = new SimpleWebhookTweeterTemplateLocator(__DIR__ . '/templates');
		$twitter = M::mock(WebhookTweeterTwitterAPI::class);

		$testData = [
			'event' => 'test',
		];
		$testDataJson = json_encode($testData, JSON_THROW_ON_ERROR);

		$testUsername = 'test';
		$testTweetId = '12345';
		$testTweetObject = new stdClass();
		$testTweetObject->user = new stdClass();
		$testTweetObject->user->screen_name = $testUsername;
		$testTweetObject->data = new stdClass();
		$testTweetObject->data->id = $testTweetId;
		$testTweetUrl = "https://twitter.com/$testUsername/status/$testTweetId";

		$baseRequest = $factory
			->createRequest('POST', 'https://example.com' . $config->webhookPath)
			->withHeader('Content-Type', 'application/json')
			->withBody($factory->createStream($testDataJson))
			->withHeader('X-Hub-Signature', 'sha256=' . hash_hmac('sha256', $testDataJson, $config->webhookSecret));
		$successResult = new WebhookTweeterResult(true, null, $testTweetUrl, $testTweetObject);

		$testDataWithTemplateData = [
			'event' => 'data',
			'data' => 'testdata',
		];
		$testDataWithTemplateDataJson = json_encode($testDataWithTemplateData, JSON_THROW_ON_ERROR);
		$baseRequestWithData = $baseRequest
			->withBody($factory->createStream($testDataWithTemplateDataJson))
			->withHeader('X-Hub-Signature', 'sha256=' . hash_hmac('sha256', $testDataWithTemplateDataJson, $config->webhookSecret))
		;

		$twitter
			->expects('sendTweet')
			->with("This is a test template.\n")
			->andReturns($testTweetObject)
		;
		$twitter
			->expects('sendTweet')
			->with("Data: " . $testDataWithTemplateData['data'] . "\n")
			->andReturns($testTweetObject)
		;

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'twitter' => $twitter,
			'request' => $baseRequest,
			'expected' => $successResult,
		];

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'twitter' => $twitter,
			'request' => $baseRequestWithData,
			'expected' => $successResult,
		];

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'twitter' => $twitter,
			'request' => $baseRequest,
			'expected' => $successResult,
		];

		$invalidMethodRequest = $baseRequest->withMethod('GET');
		$invalidMethodResult = new WebhookTweeterResult(false, 'Invalid request method', null, null);

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'twitter' => $twitter,
			'request' => $invalidMethodRequest,
			'expected' => $invalidMethodResult,
		];

		$invalidPathRequest = $baseRequest->withUri(new Uri('https://example.com/not-the-webhook-path'));
		$invalidPathResult = new WebhookTweeterResult(false, 'Invalid request path', null, null);

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'twitter' => $twitter,
			'request' => $invalidPathRequest,
			'expected' => $invalidPathResult,
		];

		$invalidSecretRequest = $baseRequest->withHeader('X-Hub-Signature', 'not-the-signature');
		$invalidSecretResult = new WebhookTweeterResult(false, 'Invalid request signature', null, null);

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'twitter' => $twitter,
			'request' => $invalidSecretRequest,
			'expected' => $invalidSecretResult,
		];

		$invalidContentTypeRequest = $baseRequest->withHeader('Content-Type', 'application/x-www-form-urlencoded');
		$invalidContentTypeResult = new WebhookTweeterResult(false, 'Invalid request content type', null, null);

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'twitter' => $twitter,
			'request' => $invalidContentTypeRequest,
			'expected' => $invalidContentTypeResult,
		];

		$invalidContentRequest = $baseRequest
			->withBody($factory->createStream('invalid-json'))
			->withHeader('X-Hub-Signature', 'sha256=' . hash_hmac('sha256', 'invalid-json', $config->webhookSecret))
		;
		$invalidContentResult = new WebhookTweeterResult(false, 'Invalid request payload', null, null);

		yield [
			'config' => $config,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'twitter' => $twitter,
			'request' => $invalidContentRequest,
			'expected' => $invalidContentResult,
		];

		$configWithoutSecret = new WebhookTweeterConfig('/webhook');
		$baseRequestWithoutSignature = $baseRequest->withoutHeader('X-Hub-Signature');

		yield [
			'config' => $configWithoutSecret,
			'renderer' => $renderer,
			'templateLocator' => $templateLocator,
			'twitter' => $twitter,
			'request' => $baseRequestWithoutSignature,
			'expected' => $successResult,
		];
	}

	/**
	 * @dataProvider requestProvider
	 * @throws JsonException
	 */
	public function testHandle(
		WebhookTweeterConfig $config,
		WebhookTweeterRenderer $renderer,
		WebhookTweeterTemplateLocator $templateLocator,
		WebhookTweeterTwitterAPI $twitter,
		RequestInterface $request,
		WebhookTweeterResult $expected,
	): void
	{
		$request->getBody()->rewind();

		$handler = new WebhookTweeterHandler($config, $renderer, $templateLocator, $twitter);
		$result = $handler->handle($request);

		static::assertEquals($expected->success, $result->success);
		static::assertEquals($expected->message, $result->message);
		static::assertEquals($expected->url, $result->url);
		static::assertEquals($expected->tweet, $result->tweet);
	}
}
