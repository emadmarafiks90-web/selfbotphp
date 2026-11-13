<?php
declare(strict_types=1);

   
                            
                                         
                      
   

const BALE_URL = 'https://next-ws.bale.ai';
const BALE_ORIGIN = 'https://web.bale.ai';
const BALE_SERVICE = 'bale.auth.v1.Auth';
const BALE_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36';

function out(string $message = ''): void
{
    echo $message . PHP_EOL;
}

function prompt(string $label, bool $hidden = false): string
{
    echo $label;

    if ($hidden && DIRECTORY_SEPARATOR !== '\\' && function_exists('shell_exec')) {
        shell_exec('stty -echo');
        try {
            $value = trim((string) fgets(STDIN));
        } finally {
            shell_exec('stty echo');
            echo PHP_EOL;
        }
        return $value;
    }

    return trim((string) fgets(STDIN));
}

function encodeVarint(int $value): string
{
    if ($value < 0) {
        throw new InvalidArgumentException('Negative varints are not supported.');
    }

    $output = '';
    while ($value > 127) {
        $output .= chr(($value & 0x7f) | 0x80);
        $value >>= 7;
    }

    return $output . chr($value);
}

function readVarint(string $data, int &$offset): ?int
{
    $result = 0;
    $shift = 0;
    $length = strlen($data);

    while ($offset < $length && $shift < 64) {
        $byte = ord($data[$offset++]);
        $result |= (($byte & 0x7f) << $shift);

        if (($byte & 0x80) === 0) {
            return $result;
        }

        $shift += 7;
    }

    return null;
}

function pbInt(int $field, int $value): string
{
    return encodeVarint($field << 3) . encodeVarint($value);
}

function pbString(int $field, string $value): string
{
    return encodeVarint(($field << 3) | 2)
        . encodeVarint(strlen($value))
        . $value;
}

function pbFrame(string $payload): string
{
    return "\x00" . pack('N', strlen($payload)) . $payload;
}

function pbRead(string $data): array
{
    $result = [];
    $offset = 0;
    $length = strlen($data);

    while ($offset < $length) {
        $tag = readVarint($data, $offset);
        if ($tag === null || $tag === 0) {
            break;
        }

        $field = $tag >> 3;
        $wire = $tag & 7;

        if ($wire === 0) {
            $value = readVarint($data, $offset);
            if ($value === null) break;
        } elseif ($wire === 2) {
            $size = readVarint($data, $offset);
            if ($size === null || $size < 0 || $offset + $size > $length) break;
            $value = substr($data, $offset, $size);
            $offset += $size;
        } elseif ($wire === 1 || $wire === 5) {
            $size = $wire === 1 ? 8 : 4;
            if ($offset + $size > $length) break;
            $value = substr($data, $offset, $size);
            $offset += $size;
        } else {
            break;
        }

        $result[$field][] = $value;
    }

    return $result;
}

function firstField(array $data, int $field, mixed $default = null): mixed
{
    return $data[$field][0] ?? $default;
}

function grpcPayload(string $body): string
{
    $offset = 0;
    $result = '';
    $length = strlen($body);

    while ($offset + 5 <= $length) {
        $flag = ord($body[$offset]);
        $size = unpack('N', substr($body, $offset + 1, 4))[1];
        $offset += 5;

        if ($offset + $size > $length) {
            break;
        }

        $chunk = substr($body, $offset, $size);
        $offset += $size;

        if ($flag === 0) {
            $result .= $chunk;
        }
    }

    return $result;
}

function httpGet(string $url, int $timeout = 30): string
{
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_USERAGENT => BALE_UA,
    ]);

    $body = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    if ($body === false) {
        throw new RuntimeException('HTTP request failed: ' . $error);
    }

    return (string) $body;
}

function findAppKey(string $content): ?string
{
    $patterns = [
        '~Web\\]:\\{id:\\d+,apiKey:"([0-9A-Fa-f]{30,})"~',
        '~apiKey:"([0-9A-Fa-f]{30,})"~',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $content, $matches)) {
            return $matches[1];
        }
    }

    return null;
}

function detectAppKey(): string
{
    $cache = __DIR__ . '/bale.appkey';

    if (is_file($cache)) {
        $cached = trim((string) file_get_contents($cache));
        if ($cached !== '') return $cached;
    }

    out('Discovering the public web client configuration...');

    $html = httpGet(BALE_ORIGIN . '/');
    $appKey = findAppKey($html);

    if ($appKey === null && preg_match_all('~(?:src|href)=["\']([^"\']+\\.js)["\']~i', $html, $matches)) {
        foreach (array_unique($matches[1]) as $source) {
            $url = str_starts_with($source, 'http')
                ? $source
                : BALE_ORIGIN . '/' . ltrim($source, '/');

            try {
                $appKey = findAppKey(httpGet($url, 60));
            } catch (Throwable) {
                continue;
            }

            if ($appKey !== null) break;
        }
    }

    if ($appKey === null) {
        throw new RuntimeException('Could not detect the web client configuration. Set BALE_APP_KEY manually.');
    }

    if (file_put_contents($cache, $appKey, LOCK_EX) === false) {
        throw new RuntimeException('Could not cache the client configuration.');
    }

    @chmod($cache, 0600);
    return $appKey;
}

function deviceHash(): string
{
    $path = __DIR__ . '/bale.device';

    if (is_file($path)) {
        $cached = trim((string) file_get_contents($path));
        if ($cached !== '') return $cached;
    }

    $value = bin2hex(random_bytes(16));

    if (file_put_contents($path, $value, LOCK_EX) === false) {
        throw new RuntimeException('Could not save the device identifier.');
    }

    @chmod($path, 0600);
    return $value;
}

function rpc(string $method, string $protobuf, string $sessionId): array
{
    $headers = [
        'User-Agent: ' . BALE_UA,
        'Origin: ' . BALE_ORIGIN,
        'Content-Type: application/grpc-web+proto',
        'App_version: 113466',
        'Browser_type: 1',
        'Browser_version: 138.0.0.0',
        'Os_type: 3',
        'Session_id: ' . $sessionId,
        'Mt_app_version: 113466',
        'Mt_browser_type: 1',
        'Mt_browser_version: 138.0.0.0',
        'Mt_os_type: 3',
        'Mt_session_id: ' . $sessionId,
    ];

    $responseHeaders = [];
    $curl = curl_init(BALE_URL . '/' . BALE_SERVICE . '/' . $method);

    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => pbFrame($protobuf),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADERFUNCTION => static function ($curl, string $header) use (&$responseHeaders): int {
            $length = strlen($header);
            $parts = explode(':', $header, 2);

            if (count($parts) === 2) {
                $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
            }

            return $length;
        },
        CURLOPT_CONNECTTIMEOUT => 20,
        CURLOPT_TIMEOUT => 45,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $body = curl_exec($curl);
    $error = curl_error($curl);
    $http = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($body === false) {
        throw new RuntimeException('Transport error: ' . $error);
    }

    $message = rawurldecode($responseHeaders['grpc-message'] ?? '');

    return [
        'http' => $http,
        'grpc_status' => $responseHeaders['grpc-status'] ?? '',
        'message' => $message,
        'body' => (string) $body,
        'payload' => grpcPayload((string) $body),
    ];
}

function showResponse(array $response): void
{
    out('HTTP Status: ' . $response['http']);
    out('gRPC Status: ' . ($response['grpc_status'] !== '' ? $response['grpc_status'] : 'not provided'));
    out('gRPC Message: ' . ($response['message'] !== '' ? $response['message'] : 'none'));
}

function normalizePhone(string $phone): string
{
    $phone = preg_replace('/\\D+/', '', $phone) ?? '';

    if (str_starts_with($phone, '00')) {
        $phone = substr($phone, 2);
    } elseif (str_starts_with($phone, '09')) {
        $phone = '98' . substr($phone, 1);
    }

    if (!preg_match('/^989\\d{9}$/', $phone)) {
        throw new InvalidArgumentException('Enter a valid Iranian mobile number, for example 989111111111.');
    }

    return $phone;
}

function saveSession(string $body): void
{
    $path = __DIR__ . '/bale.session';

    if (file_put_contents($path, $body, LOCK_EX) === false) {
        throw new RuntimeException('Could not save the session.');
    }

    @chmod($path, 0600);
    out('Session saved locally.');
}

try {
    if (!extension_loaded('curl')) {
        throw new RuntimeException('The PHP cURL extension is required.');
    }

    out('========================================');
    out('          EM.ir | BALE PHP');
    out('========================================');

    $phone = normalizePhone(prompt('Phone number (country code 98): '));
    $device = deviceHash();

    $appKey = getenv('BALE_APP_KEY') ?: '';
    if ($appKey === '') {
        try {
            $appKey = detectAppKey();
        } catch (Throwable $error) {
            out('Automatic configuration discovery failed: ' . $error->getMessage());
            $appKey = prompt('Web client App Key: ');
        }
    }

    if ($appKey === '') {
        throw new RuntimeException('App Key is required.');
    }

    $deviceTitle = 'Chrome_138.0.0.0, Windows';
    $sessionId = (string) ((int) floor(microtime(true) * 1000));

    out('');
    out('[1/3] Starting phone authentication...');

    $options = pbInt(1, 1);
    $request = pbInt(1, (int) $phone)
        . pbInt(2, 4)
        . pbString(3, $appKey)
        . pbString(4, hex2bin($device))
        . pbString(5, $deviceTitle)
        . pbInt(9, 1)
        . pbString(10, $options);

    $start = rpc('StartPhoneAuth', $request, $sessionId);
    showResponse($start);

    if ($start['message'] !== '') {
        throw new RuntimeException('StartPhoneAuth failed: ' . $start['message']);
    }

    if ($start['http'] < 200 || $start['http'] >= 300 || $start['payload'] === '') {
        throw new RuntimeException('The server did not return a valid authentication response.');
    }

    $decoded = pbRead($start['payload']);
    $transactionHash = firstField($decoded, 1);

    if (!is_string($transactionHash) || $transactionHash === '') {
        throw new RuntimeException('The transaction identifier was not found in the response.');
    }

    out('Authentication request accepted.');

    out('');
    out('[2/3] Code verification');

    $code = prompt('Verification code: ');
    if ($code === '') {
        throw new RuntimeException('Verification code cannot be empty.');
    }

    $jwtFlag = pbInt(1, 1);
    $validateRequest = pbString(1, $transactionHash)
        . pbString(2, $code)
        . pbString(3, $jwtFlag);

    $validated = rpc('ValidateCode', $validateRequest, $sessionId);
    showResponse($validated);

    if ($validated['message'] === 'password needed for login') {
        out('Two-step verification is enabled.');
        $password = prompt('Two-step verification password: ', true);

        if ($password === '') {
            throw new RuntimeException('Password cannot be empty.');
        }

        $passwordRequest = pbString(1, $transactionHash)
            . pbString(2, $password)
            . pbString(3, $jwtFlag);

        $validated = rpc('ValidatePassword', $passwordRequest, $sessionId);
        showResponse($validated);
    }

    if ($validated['message'] !== '') {
        throw new RuntimeException('Authentication failed: ' . $validated['message']);
    }

    if ($validated['http'] < 200 || $validated['http'] >= 300 || $validated['payload'] === '') {
        throw new RuntimeException('The authentication response was not valid. No session was saved.');
    }

    out('');
    out('[3/3] Saving the local session...');
    saveSession($validated['body']);

    out('');
    out('Authentication flow completed.');
    out('Keep bale.session private. Never share or commit it.');

} catch (Throwable $error) {
    out('');
    out('[ERROR] ' . $error->getMessage());
    exit(1);
}
