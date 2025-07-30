<?php

namespace AntiCmsBuilder;

class SortHelper
{
    public static function sortComponents(array &$components, string $sortBy = 'label', string $order = 'asc'): void
    {
        usort($components, function ($a, $b) use ($sortBy, $order) {
            return $order === 'asc'
                ? strcmp($a[$sortBy], $b[$sortBy])
                : strcmp($b[$sortBy], $a[$sortBy]);
        });
    }
}

