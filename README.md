# nargotik/early-hints

Tiny PHP polyfill to send **HTTP `103 Early Hints`** easily:

```php
use function Nargotik\EarlyHints\early_hints;

early_hints([
  ['href' => '/assets/app.css', 'rel' => 'preload', 'as' => 'style'],
  ['href' => '/assets/app.js',  'rel' => 'preload', 'as' => 'script'],
]);

// later:
header('Content-Type: text/html; charset=UTF-8');
http_response_code(200);
