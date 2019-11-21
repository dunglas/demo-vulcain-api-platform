<?php

// Comment line 219 in api/vendor/symfony/http-client/Response/CurlResponse.php to make this working
// Yeah... They are still some bugs in Symfony, but we're on it!

require __DIR__.'/api/vendor/autoload.php';

use Psr\Log\LogLevel;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpKernel\Log\Logger;

$start = microtime(true);
$i = 0;

$client = new CurlHttpClient(['base_uri' => 'https://localhost:8443', 'verify_peer' => false], 500, 500);
//$client->setLogger(new Logger(LogLevel::DEBUG));

$conferences = $client->request(
    'GET',
    '/conferences',
    ['headers' => ['Preload' => '/hydra:member/*/@id/sessions/*/feedback/*']]
)->toArray();
foreach ($conferences['hydra:member'] as $conferenceRel) {
    $conference = $client->request('GET', $conferenceRel['@id'])->toArray();
    foreach ($conference['sessions'] as $sessionUrl) {
        $session = $client->request('GET', $sessionUrl)->toArray();
        foreach ($session['feedback'] as $feedbackURL) {
            $feedback = $client->request('GET', $feedbackURL)->toArray();

            if (++$i === 100) {
                $time = microtime(true) - $start;
                echo "All data retrieved in {$time}".PHP_EOL;
            }
        }
    }
}
