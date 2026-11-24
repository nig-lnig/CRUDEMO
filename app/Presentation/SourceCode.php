<?php

namespace App\Presentation;

use ReflectionMethod;

/**
 * Extrait le code source RÉEL d'une méthode de contrôleur.
 * Le code affiché sur les slides est donc toujours celui qui s'exécute.
 */
class SourceCode
{
    public static function method(string $class, string $method): string
    {
        $reflection = new ReflectionMethod($class, $method);

        $lines = file($reflection->getFileName(), FILE_IGNORE_NEW_LINES);
        $slice = array_slice(
            $lines,
            $reflection->getStartLine() - 1,
            $reflection->getEndLine() - $reflection->getStartLine() + 1,
        );

        return self::dedent($slice);
    }

    public static function file(string $relativePath): string
    {
        return rtrim(file_get_contents(base_path($relativePath)));
    }

    /**
     * @param  array<int, string>  $lines
     */
    private static function dedent(array $lines): string
    {
        $indent = PHP_INT_MAX;

        foreach ($lines as $line) {
            if (trim($line) !== '') {
                $indent = min($indent, strlen($line) - strlen(ltrim($line, ' ')));
            }
        }

        return implode("\n", array_map(
            fn (string $line) => substr($line, $indent === PHP_INT_MAX ? 0 : $indent),
            $lines,
        ));
    }
}
