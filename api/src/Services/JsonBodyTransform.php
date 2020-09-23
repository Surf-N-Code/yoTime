<?php


namespace App\Services;


use Symfony\Component\HttpFoundation\Request;

class JsonBodyTransform
{
    public function transformJsonBody(Request $request): Request
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(sprintf("Error parsing JSON: %s", json_last_error()), 400);
        }

        if ($data === null) {
            return $request;
        }

        $request->request->replace($data);

        return $request;
    }
}
