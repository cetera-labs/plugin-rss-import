<?php

namespace RssImport;

use Exception;
use GuzzleHttp\Client;

final class Utils
{

    /**
     * Проверяет, что путь ведёт к валидному RSS 2.0 feed.
     * @throws Exception
     */
    public static function validateRssByPath(string $path): bool
    {
        if (!filter_var($path, FILTER_VALIDATE_URL)) {
            throw new Exception('Некорректный URL');
        }

        $content = @file_get_contents($path);
        if ($content === false) {
            throw new Exception('Не удалось получить содержимое по указанному URL');
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);
        if ($xml === false) {
            throw new Exception('Содержимое не является корректным XML');
        }

        if (strtolower($xml->getName()) !== 'rss') {
            throw new Exception('Корневой элемент не является RSS');
        }

        return true;
    }


    /**
     * @throws Exception
     */
    public static function processImage(string $imageUrl): string
    {

        $uploadDir = DOCROOT . '/uploads/';
//        if (!is_dir($uploadDir)) {
//            mkdir($uploadDir, 0777, true);
//        }
        $ext = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
        $filename = uniqid('rss_', true) . ($ext ? '.' . $ext : '');
        $filepath = $uploadDir . $filename;

        $client = new Client([
            'verify' => false,
            'timeout' => 10,
        ]);

        try {
            $response = $client->get($imageUrl, [
                'http_errors' => false,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (compatible; CeteraCMS RSS Import/1.0; +https://cetera.ru)'
                ]
            ]);
            if ($response->getStatusCode() === 200) {
                file_put_contents($filepath, $response->getBody()->getContents());
                return '/uploads/' . $filename;
            }
        } catch (\Exception $e) {
            throw new Exception('Ошибка при загрузке изображения: ' . $e->getMessage());
        }

        throw new Exception('Не удалось загрузить изображение по указанному URL');
    }

    public static function respondAndExit(array $data): void
    {
        die(json_encode($data));
    }
}
