<?php

$statusMessagesMap = [
    200 => ['status' => 'success', 'message' => 'Ok'],
    201 => ['status' => 'success', 'message' => 'New resource has been created.'],
    204 => ['status' => 'success', 'message' => 'The resource was successfully deleted.'],
    400 => ['status' => 'error', 'message' => 'Bad Request: The request was invalid or cannot be served.'],
    401 => ['status' => 'error', 'message' => 'Unauthorized: The request requires an user authentication.'],
    403 => ['status' => 'error', 'message' => 'Forbidden: access is not allowed.'],
    404 => ['status' => 'error', 'message' => 'Not found: There is no resource behind the URI.'],
    422 => ['status' => 'error', 'message' => 'Unprocessable Entity: Could not process due to validation errors.'],
];

$defaultOptions = [
    'data' => [],
    'httpCode' => 200,
    'message' => '',
    'errors' => []
];

Response::macro('apiResponse',
    function ($options) use (
        $statusMessagesMap,
        $defaultOptions
    ) {
        $options = array_merge($defaultOptions, $options);

        $status = $statusMessagesMap[$options['httpCode']]['status'];
        $message = $options['message'] ? $options['message'] : $statusMessagesMap[$options['httpCode']]['message'];
        $response = ['status' => $status, 'data' => $options['data'], 'message' => $message];

        if ($options['errors']) {
            $response['errors'] = $options['errors'];
        }

        /**
         * Retornando objetos customizados para paginação
         */
        if (is_object($options['data']) && is_subclass_of($options['data'], 'Illuminate\Contracts\Pagination\Paginator')) {
            $results = $options['data']->toArray()['data'];
            $response = [
                'status' => $status,
                'data' => $results,
                'paging' => [
                    'total' => $options['data']->total(),
                    'perPage' => $options['data']->perPage(),
                    'currentPage' => $options['data']->currentPage(),
                    'lastPage' => $options['data']->lastPage(),
                    'from' => $options['data']->firstItem(),
                    'to' => $options['data']->lastItem(),
                    'previous' => $options['data']->previousPageUrl(),
                    'next' => $options['data']->nextPageUrl()
                ],
                'message' => $message
            ];
        }

        if ($status == 'error' || empty($options['data'])) {
            unset($response['data']);
        }

        return Response::json($response, $options['httpCode']);

    });