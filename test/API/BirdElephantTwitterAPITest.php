<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTweeter\API;

use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \ricardoboss\WebhookTweeter\API\BirdElephantTwitterAPI
 *
 * @internal
 */
class BirdElephantTwitterAPITest extends TestCase {
	public function tweetObjectProvider(): iterable {
		$a = new stdClass();
		$a->user = new stdClass();
		$a->user->screen_name = 'test';
		$a->data = new stdClass();
		$a->data->id = 12345;

		yield [$a, 'https://twitter.com/test/status/12345'];
	}

	/**
	 * @dataProvider tweetObjectProvider
	 */
	public function testGetTweetUrl(object $tweet, ?string $expectedUrl): void {
		$api = new BirdElephantTwitterAPI();
		$url = $api->getTweetUrl($tweet);

		static::assertEquals($expectedUrl, $url);
	}
}
