<?php

use GuzzleHttp\Client as Guzzle;

class Elastic
{
    private $config = [
        'host' => '127.0.0.1',
        'user' => null,
        'pass' => null,
    ];

    public function __construct(
        array $config = []
    ) {
        $this->config = array_merge(
            $this->config,
            $config
        );
    }

    public function indices()
    {
        try {
            $url = $this->config['host'] .
                '/_cat/indices?format=JSON';
            $payload = $this->addAuth([]);

            $response = $this->newGuzzle()->get(
                $url,
                $payload
            );

            $statusCode = $response->getStatusCode();
            if ($statusCode != 200) {
                throw new Exception('StatusCode for "search" method different from 200');
            }

            $content = $response
                ->getBody()
                ->getContents();

            return json_decode($content, true);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function reindex(
        string $index,
        string $newIndex
    ) {
        try {
            $payload = [
                'source' => [
                    'index' => $index,
                ],
                'dest' => [
                    'index' => $newIndex,
                ],
            ];

            $url = $this->config['host'] .
                '/_reindex';
            $payload = [
                'json' => $payload,
            ];
            $payload = $this->addAuth($payload);

            $response = $this->newGuzzle()->post(
                $url,
                $payload
            );

            $statusCode = $response->getStatusCode();
            if ($statusCode != 200) {
                throw new Exception('StatusCode for "search" method different from 200');
            }

            $content = $response
                ->getBody()
                ->getContents();

            return json_decode($content, true);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function stats(
        string $index
    ) {
        try {
            $url = $this->config['host'] .
                '/' .
                $index .
                '/_stats';
            $payload = $this->addAuth([]);

            $response = $this->newGuzzle()->get(
                $url,
                $payload
            );

            $statusCode = $response->getStatusCode();
            if ($statusCode != 200) {
                throw new Exception('StatusCode for "search" method different from 200');
            }

            $content = $response
                ->getBody()
                ->getContents();

            return json_decode($content, true);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function del(
        string $index
    ) {
        try {
            $url = $this->config['host'] .
                '/' .
                $index;
            $payload = $this->addAuth([]);

            $response = $this->newGuzzle()->delete(
                $url,
                $payload
            );

            $statusCode = $response->getStatusCode();
            if ($statusCode != 200) {
                throw new Exception('StatusCode for "search" method different from 200');
            }

            $content = $response
                ->getBody()
                ->getContents();

            return json_decode($content, true);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function addAuth(
        array $payload
    ) {
        if (
            !empty($this->config['user']) &&
            !empty($this->config['pass'])
        ) {
            $payload['auth'] = [
                $this->config['user'],
                $this->config['pass']
            ];
        }

        return $payload;
    }

    public function newGuzzle()
    {
        return new Guzzle();
    }
}
