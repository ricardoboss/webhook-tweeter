<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTweeter\Simple;

use ricardoboss\WebhookTweeter\WebhookTweeterRenderer;
use ricardoboss\WebhookTweeter\WebhookTweeterTemplate;

class SimpleWebhookTweeterRenderer implements WebhookTweeterRenderer
{
	public function render(WebhookTweeterTemplate $template, array $data): string
	{
		$text = $template->getContents();

		foreach ($data as $name => $item) {
			$text = str_replace("{{ $name }}", $item, $text);
		}

		return $text;
	}
}
