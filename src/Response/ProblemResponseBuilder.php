<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Response;

use JsonException;
use RuntimeException;

final class ProblemResponseBuilder
{
    private function __construct()
    {
        // prevent instantiation
    }

    /**
     * @param array<string, mixed> $additionalData
     */
    public static function createBody(int $statusCode, string $reasonPhrase, string $detail = '', array $additionalData = []): string
    {
        $data = [
            'type' => 'https://www.rfc-editor.org/rfc/rfc9110#name-' . $statusCode . '-' . strtolower(str_replace(' ', '-', $reasonPhrase)),
            'title' => $reasonPhrase,
        ];
        if ($detail !== '') {
            $data['detail'] = $detail;
        }
        if ($additionalData !== []) {
            $data = array_merge($data, $additionalData);
        }
        try {
            return json_encode($data, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Failed to JSON-encode problem response body', 1743667595, $e);
        }
    }
}
