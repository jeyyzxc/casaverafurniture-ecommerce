<?php

namespace App\Helpers;

/**
 * Number Helper
 * Provides utilities for number parsing and formatting
 */
class NumberHelper
{
    /**
     * Parse a value to float, handling strings with currency symbols
     */
    public static function parseFloat(mixed $value, ?float $default = null): ?float
    {
        if ($value === null) {
            return $default;
        }

        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            // Remove currency symbols, commas, and other non-numeric characters except decimal point and minus
            $cleaned = preg_replace('/[^0-9.-]/', '', $value);
            $parsed = filter_var($cleaned, FILTER_VALIDATE_FLOAT);
            
            return $parsed !== false ? $parsed : $default;
        }

        return $default;
    }

    /**
     * Parse a value to integer
     */
    public static function parseInt(mixed $value, ?int $default = null): ?int
    {
        if ($value === null) {
            return $default;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) $value;
        }

        if (is_string($value)) {
            $cleaned = preg_replace('/[^0-9-]/', '', $value);
            $parsed = filter_var($cleaned, FILTER_VALIDATE_INT);
            
            return $parsed !== false ? $parsed : $default;
        }

        return $default;
    }

    /**
     * Format price with currency symbol
     */
    public static function formatPrice(float $price, string $currency = '₱'): string
    {
        return $currency . number_format($price, 2, '.', ',');
    }

    /**
     * Calculate percentage discount
     */
    public static function calculateDiscountPercentage(float $originalPrice, float $salePrice): float
    {
        if ($originalPrice <= 0) {
            return 0;
        }

        return round((($originalPrice - $salePrice) / $originalPrice) * 100, 2);
    }
}
