<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTweeter;

use JsonException;
use Psr\Http\Message\RequestInterface;

class WebhookTweeterHandler {
	public const SignatureHeader = 'X-Hub-Signature-256';
	public const SignatureAlgorithm = 'sha256';

	public function __construct(
		private readonly WebhookTweeterConfig $config,
		private readonly WebhookTweeterRenderer $renderer,
		private readonly WebhookTweeterTemplateLocator $templateLocator,
		private readonly WebhookTweeterTwitterAPI $twitter,
	) {}

	public function handle(RequestInterface $request): WebhookTweeterResult {
		$result = $this->verifyRequestHeaders($request);
		if ($result !== null) {
			return $result;
		}

		$body = $request->getBody()->getContents();

		if (!$this->verifySignature($request, $body)) {
			return WebhookTweeterResult::failure('Invalid request signature');
		}

		try {
			$payload = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
		} catch (JsonException $e) {
			return WebhookTweeterResult::failure("Invalid request payload: " . $e->getMessage());
		}

		$renderedTemplate = $this->renderTemplate($payload);
		$tweet = $this->twitter->sendTweet($renderedTemplate);
		$url = $this->twitter->getTweetUrl($tweet);

		return WebhookTweeterResult::success($url, $tweet);
	}

	private function verifyRequestHeaders(RequestInterface $request): ?WebhookTweeterResult {
		$method = $request->getMethod();
		if ($method !== 'POST') {
			return WebhookTweeterResult::failure("Invalid request method: $method");
		}

		if ($this->config->webhookPath !== null) {
			$webhookPath = (string) $this->config->webhookPath;
			$actualPath = $request->getUri()->getPath();
			if ($actualPath !== $webhookPath) {
				return WebhookTweeterResult::failure("Invalid request path: $actualPath");
			}
		}

		$contentType = $request->getHeaderLine('Content-Type');
		if ($contentType !== 'application/json') {
			return WebhookTweeterResult::failure("Invalid request content type: $contentType");
		}

		return null;
	}

	private function verifySignature(RequestInterface $request, string $body): bool {
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

	private function renderTemplate(array $data): string {
		$template = $this->templateLocator->getMatchingTemplate($data) ?? $this->templateLocator->getDefaultTemplate();

		return $this->renderer->render($template, $data);
	}
}
