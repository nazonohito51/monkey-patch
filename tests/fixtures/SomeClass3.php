<?php
declare(strict_types=1);

namespace MonkeyPatch\Tests\Fixtures;

class SomeClass3
{
    public function someMethod()
    {
        $client = new \GuzzleHttp\Client();
        return $client->request('GET', 'https://your.production.env.com/api/end_point');
    }
}
