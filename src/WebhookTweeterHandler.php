<?php
declare(strict_types=1);

namespace Ricardoboss\WebhookTweeter;

use Coderjerk\BirdElephant\BirdElephant;
use Coderjerk\BirdElephant\Compose\Tweet;
use JsonException;
use Psr\Http\Message\RequestInterface;

class WebhookTweeterHandler
{
	private readonly BirdElephant $twitter;

	public function __construct(
		private readonly WebhookTweeterConfig $config,
		private readonly WebhookTweeterRenderer $renderer,
		private readonly WebhookTweeterTemplateLocator $templateLocator,
	)
	{
		$this->twitter = new BirdElephant([
			'bearer_token' => (string) $this->config->bearerToken,
			'consumer_key' => (string) $this->config->consumerKey,
			'consumer_secret' => (string) $this->config->consumerSecret,
		]);
	}

	/**
	 * @param RequestInterface $request
	 * @return WebhookTweeterResult
	 * @throws JsonException
	 */
	public function handle(RequestInterface $request): WebhookTweeterResult
	{
		if (!$this->matchesWebhookFormat($request)) {
			return new WebhookTweeterResult(false, 'Invalid request format', null);
		}

		if (!$this->verifySignature($request)) {
			return new WebhookTweeterResult(false, 'Invalid request secret', null);
		}

		$payload = $this->getPayload($request);
		$renderedTemplate = $this->renderTemplate($payload);
		$tweet = $this->sendTweet($renderedTemplate);

		$url = $this->getTweetUrl($tweet);

		return new WebhookTweeterResult(true, null, $tweet->url);
	}

	private function matchesWebhookFormat(RequestInterface $request): bool
	{
		if ($request->getMethod() !== 'POST') {
			return false;
		}

		if ($request->getHeaderLine('Content-Type') !== 'application/json') {
			return false;
		}

		return true;
	}

	private function verifySignature(RequestInterface $request): bool
	{
		if ($this->config->webhookSecret === null) {
			return true;
		}

		$body = $request->getBody()->getContents();

		$signature = $request->getHeaderLine('X-Hub-Signature-256');

		$hash = hash_hmac('sha256', $body, $this->config->webhookSecret);

		return hash_equals($hash, $signature);
	}

	public function renderTemplate(array $payload): string
	{
		$template = $this->templateLocator->getMatchingTemplate($payload['event']) ?? $this->templateLocator->getDefaultTemplate();

		return $this->renderer->render($template, $payload);
	}

	/**
	 * @throws JsonException
	 */
	private function getPayload(RequestInterface $request): array
	{
		$body = $request->getBody()->getContents();

		return json_decode($body, true, flags: JSON_THROW_ON_ERROR);
	}

	private function sendTweet(string $text): object
	{
		$tweet = (new Tweet)->text($text);

		return $this->twitter->tweets()->tweet($tweet);
	}

	private function getTweetUrl(object $tweet): string
	{
		return sprintf('https://twitter.com/%s/status/%s', $tweet->user->screen_name, $tweet->data->id);
	}
}
