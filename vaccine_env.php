<?php
/**
 * Reads a value from the vaccination module's .env file.
 *
 * The file is parsed once per request. Returns an empty string when the
 * file or key is missing so callers can decide how to fail.
 */
function vaccine_env(string $key): string
{
    static $config = null;

    if ($config === null) {
        $envPath = __DIR__ . '/.env';
        $parsed = (is_file($envPath) && is_readable($envPath))
            ? parse_ini_file($envPath, false, INI_SCANNER_RAW)
            : false;
        $config = is_array($parsed) ? $parsed : [];
    }

    return isset($config[$key]) ? (string) $config[$key] : '';
}
