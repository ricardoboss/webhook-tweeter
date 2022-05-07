# webhook-tweeter

This package aims to provide simple interfaces to implement a webhook-based tweeting system.

This can, for example, be used to tweet a release notification when a new release is published to a GitHub repository.

## Installation

```bash
composer require ricardoboss/webhook-tweeter
```

## Usage

```php
<?php

use Ricardoboss\WebhookTweeter\WebhookTweeterConfig;
use Ricardoboss\WebhookTweeter\Simple\SimpleWebhookTweeterRenderer;
use Ricardoboss\WebhookTweeter\Simple\SimpleWebhookTweeterTemplateLocator;
use Ricardoboss\WebhookTweeter\WebhookTweeterHandler;

// 1. Create a config object
// you can also pass \Stringable objects instead of strings
$config = new WebhookTweeterConfig(
    'bearer_token',
    'consumer_key',
    'consumer_secret',
    'webhook_url',
    'webhook_secret' // nullable
);

// 2. Create an instance of WebhookTweeterRenderer
// either use your own renderer or use the simple renderer
$renderer = new SimpleWebhookTweeterRenderer();

// 3. Create a template locator instance
// the simple locator looks for files in the given directory and the given extension (name is passed to the getMatchingTemplate method)
$locator = new SimpleWebhookTweeterTemplateLocator(__DIR__ . '/templates', '.md');

// 4. Create a WebhookTweeterHandler instance
$handler = new WebhookTweeterHandler($config, $renderer, $locator);

// 5. Get a PSR-7 request object
$request = /* get your request implementation */;

// 6. Handle the request (sends a rendered tweet)
$result $handler->handle($request);
```

The `$result` variable holds a `WebhookTweeterResult` instance.
The result has the following properties:

- `$result->success`: `true` if the tweet was sent successfully, `false` otherwise
- `$result->message`: an error message if the tweet was not sent successfully
- `$result->url`: a URL to the tweet
- `$result->tweet`: the tweet object returned from the Twitter API

## Credits

Thanks to [danieldevine](https://github.com/danieldevine) for creating [BirdElephant](https://github.com/danieldevine/bird-elephant) and providing a neat PHP interface for the Twitter V2 API!

## License

This project is licensed under the MIT license. Read more about it [here](./LICENSE).
