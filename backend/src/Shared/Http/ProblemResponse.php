<?php

declare(strict_types=1);

namespace App\Shared\Http;

use Symfony\Component\HttpFoundation\JsonResponse;

final class ProblemResponse
{
    /**
     * @param array<string, mixed> $extensions
     * @param array<string, mixed> $headers
     */
    public static function create(
        int $status,
        string $code,
        string $title,
        string $detail,
        array $extensions = [],
        array $headers = [],
    ): JsonResponse {
        $response = new JsonResponse(
            [
                'type' => 'about:blank',
                'title' => $title,
                'status' => $status,
                'code' => $code,
                'detail' => $detail,
                ...$extensions,
            ],
            $status,
            [...$headers, 'Content-Type' => 'application/problem+json'],
        );

        $response->setEncodingOptions(JsonResponse::DEFAULT_ENCODING_OPTIONS | \JSON_UNESCAPED_UNICODE);

        return $response;
    }
}
