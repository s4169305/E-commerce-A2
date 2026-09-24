<?php

function square_config_value(string $name, string $default = ''): string
{
    foreach ([
        getenv($name),
        $_ENV[$name] ?? null,
        $_SERVER[$name] ?? null,
        $default,
    ] as $value) {
        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed !== '') {
                return $trimmed;
            }
        }
    }

    return $default;
}

// Storing the developer credentials for authentication and for making API requests to the Square Sandbox environment.
$squareApplicationId = 'sandbox-sq0idb-9RyC10uXmFkS3tq0E6rRSQ';
$squareLocationId = 'LYTT4KZXYF2WW';
$squareAccessToken = 'EAAAl7HgEPmi-uMtjuWmiLPdM9gifjaNcUuQYB8Rlykf6MytTHGLjlMkzYdzIxC1';