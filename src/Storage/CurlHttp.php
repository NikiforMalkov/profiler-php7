<?php 


namespace SpiralPackages\Profiler\Storage;

//TODO: move to another place
class CurlHttp
{
    public function request(string $method, string $url, array $options = [])
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_URL, $url);

        $headers = $options['headers'] ?? [];
        if (isset($options['json'])) {
            $headers[] = 'Content-Type: application/json';
        }
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if (isset($options['json'])) {
            $jsonBody = json_encode($options['json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception('cURL error: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return [
            'status_code' => $httpCode,
            'body' => $response,
        ];
    }
}

