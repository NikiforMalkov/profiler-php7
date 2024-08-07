<?php

declare(strict_types=1);

namespace SpiralPackages\Profiler\Storage;

use SpiralPackages\Profiler\Converter\ConverterInterface;
use SpiralPackages\Profiler\Converter\NullConverter;

final class WebStorage implements StorageInterface
{
    private  CurlHttp $httpClient;
    private  string $endpoint;
    private  string $method = 'POST';
    private array $options = [];
    private  ConverterInterface $converter;
    
    public function __construct(
        CurlHttp $httpClient,
        string $endpoint,
        string $method = 'POST',
        $options = [],
        $converter = null
    ) {
        $this->httpClient = $httpClient;
        $this->endpoint = $endpoint;
        $this->method = $method;
        $this->options = $options;
        if (!$converter) {
            $this->converter = new NullConverter();
        } else {
            $this->converter = $converter;
        }
    }

    public function store(string $appName, array $tags, \DateTimeInterface $date, array $data): void
    {
        $this->options['json'] = [
            'profile' => $this->converter->convert($data),
            'tags' => $tags,
            'app_name' => $appName,
            'hostname' => \gethostname(),
            'date' => $date->getTimestamp(),
        ];

        $this->httpClient->request($this->method, $this->endpoint, $this->options);
    }
}
