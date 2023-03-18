<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTweeter;

use JsonException;
use Psr\Http\Message\RequestInterface;

class WebhookTweeterHandler
{
	public const SignatureHeader = 'X-Hub-Signature-256';
	public const SignatureAlgorithm = 'sha256';

	public function __construct(
		private readonly WebhookTweeterConfig $config,
		private readonly WebhookTweeterRenderer $renderer,
		private readonly WebhookTweeterTemplateLocator $templateLocator,
		private readonly WebhookTweeterTwitterAPI $twitter,
	)
	{
	}

	public function handle(RequestInterface $request): WebhookTweeterResult
	{
		$method = $request->getMethod();
		if ($method !== 'POST') {
			return new WebhookTweeterResult(false, "Invalid request method: $method", null, null);
		}

		$contentType = $request->getHeaderLine('Content-Type');
		if ($contentType !== 'application/json') {
			return new WebhookTweeterResult(false, "Invalid request content type: $contentType", null, null);
		}

		$path = $request->getUri()->getPath();
		if ($path !== (string) $this->config->webhookPath) {
			return new WebhookTweeterResult(false, "Invalid request path: $path", null, null);
		}

		$body = $request->getBody()->getContents();

		if (!$this->verifySignature($request, $body)) {
			return new WebhookTweeterResult(false, 'Invalid request signature', null, null);
		}

		try {
			$payload = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
		} catch (JsonException $e) {
			return new WebhookTweeterResult(false, "Invalid request payload: " . $e->getMessage(), null, null);
		}

		if (!isset($payload['event'])) {
			return new WebhookTweeterResult(false, "Missing 'event' key in payload", null, null);
		}

		$renderedTemplate = $this->renderTemplate($payload);
		$tweet = $this->twitter->sendTweet($renderedTemplate);
		$url = $this->getTweetUrl($tweet);

		return new WebhookTweeterResult(true, null, $url, $tweet);
	}

	private function verifySignature(RequestInterface $request, string $body): bool
	{
		if ($this->config->webhookSecret === null) {
			return true;
		}

		$signature = $request->getHeaderLine(self::SignatureHeader);
		if (!str_starts_with($signature, self::SignatureAlgorithm . '=')) {
			return false;
		}

		$signature = substr($signature, strlen(self::SignatureAlgorithm) + 1);

		$hash = hash_hmac(self::SignatureAlgorithm, $body, (string) $this->config->webhookSecret);

		return hash_equals($hash, $signature);
	}

	private function renderTemplate(array $payload): string
	{
		$template = $this->templateLocator->getMatchingTemplate($payload['event']) ?? $this->templateLocator->getDefaultTemplate();

		return $this->renderer->render($template, $payload);
	}

	private function getTweetUrl(object $tweet): string
	{
		return sprintf('https://twitter.com/%s/status/%s', $tweet->user->screen_name, $tweet->data->id);
	}
}
