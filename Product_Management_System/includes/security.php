<?php
class Security {
    // Enhanced sanitize string input with additional security measures
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        
        if (is_null($input) || !is_string($input)) {
            return '';
        }

        // Block any input containing script tags or XSS patterns immediately
        $xssPatterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/<\s*script/im',
            '/<\s*\/\s*script/im',
            '/javascript:/im',
            '/onclick/im',
            '/onload/im',
            '/onerror/im',
            '/onmouseover/im',
            '/<img[^>]+src[^>]*>/im',
            '/alert\s*\(/im',
            '/eval\s*\(/im',
            '/document\.cookie/im',
            '/document\.write/im',
            '/document\.location/im',
            '/window\.location/im',
            '/<iframe/im',
            '/<embed/im',
            '/<object/im',
            '/base64/im',
            '/prompt\s*\(/im',
            '/confirm\s*\(/im'
        ];

        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                throw new Exception("Potential XSS attack detected");
            }
        }
        
        // Remove invisible characters and null bytes
        $input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F\x80-\x9F]/u', '', $input);
        
        // Enhanced SQL injection prevention
        $sqlPatterns = [
            '/\b(union\s+all\s+select|union\s+select|select\s+from|insert\s+into|delete\s+from|update\s+set|drop\s+table|alter\s+table|exec\s+xp_|exec\s+sp_)\b/i',
            '/[\s\']+(OR|AND)\s+[\'"]*\s*[\d\w]+\s*[=<>]+/i',
            '/[\'"];.*?--/i',
            '/\b(admin|users|recipe_db|batch_db)\b.*?(--|#|\/\*)/i',
            '/\b(benchmark|sleep|delay|waitfor)\s*\(/i',
            '/\b(load_file|outfile|dumpfile)\s*\(/i',
            '/\b(group_concat|concat_ws|concat)\s*\(/i',
            '/\b(information_schema|sysusers|sysobjects)\b/i',
        ];
        
        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                throw new Exception("Potential SQL injection detected");
            }
        }
        
        // Remove all HTML tags
        $input = strip_tags($input);
        
        // Convert special characters to HTML entities
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8', true);
        
        // Additional cleaning
        $input = str_replace(
            ['&amp;lt;', '&amp;gt;', '&amp;quot;', '&amp;amp;', '\\', '/', '*'],
            ['<', '>', '"', '&', '', '', ''],
            $input
        );
        
        return $input;
    }

    // Enhanced validation for recipe inputs
    public static function validateRecipeInput($input, $type = 'text') {
        $input = self::sanitizeInput($input);
        
        switch ($type) {
            case 'recipe_name':
                // Only allow letters, numbers, spaces, and basic punctuation
                if (!preg_match('/^[A-Za-z0-9\s\-.,()]+$/', $input)) {
                    throw new Exception("Recipe name contains invalid characters");
                }
                if (strlen($input) > 100) {
                    throw new Exception("Recipe name is too long");
                }
                break;
                
            case 'ingredient':
                // Strictly only allow letters, numbers, and basic punctuation
                if (!preg_match('/^[A-Za-z0-9\s\-.,()]+$/', $input)) {
                    throw new Exception("Ingredient contains invalid characters");
                }
                break;
                
            case 'step':
                // More restrictive pattern for steps
                if (!preg_match('/^[A-Za-z0-9\s\-.,()°F℃]+$/', $input)) {
                    throw new Exception("Step contains invalid characters");
                }
                break;
        }
        
        return $input;
    }

    // Validate numeric input with enhanced security
    public static function validateNumeric($number, $min = null, $max = null) {
        // Remove any non-numeric characters except decimal point and minus sign
        $number = preg_replace('/[^0-9.-]/', '', $number);
        
        if (!is_numeric($number)) {
            return false;
        }
        
        $number = floatval($number);
        
        if ($min !== null && $number < $min) {
            return false;
        }
        if ($max !== null && $number > $max) {
            return false;
        }
        
        return $number;
    }

    // Validate date format with enhanced security
    public static function validateDate($date, $format = 'Y-m-d') {
        // Remove any potentially harmful characters
        $date = preg_replace('/[^0-9\-\/]/', '', $date);
        
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    // New method to sanitize HTML content while allowing specific tags
    public static function sanitizeHTML($input, $allowedTags = ['br', 'p', 'strong', 'em']) {
        if (empty($input)) {
            return '';
        }
        
        // Convert special characters to HTML entities
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Only allow specific HTML tags
        $allowedTags = array_map(function($tag) {
            return "<$tag>";
        }, $allowedTags);
        
        return strip_tags($input, implode('', $allowedTags));
    }

    // New method to sanitize and validate file paths
    public static function sanitizeFilePath($path) {
        // Remove any potentially dangerous characters
        $path = preg_replace('/[^a-zA-Z0-9\/_.-]/', '', $path);
        // Remove any attempts to navigate up directories
        $path = str_replace('../', '', $path);
        return $path;
    }

    // New method to encode data for safe JSON output
    public static function jsonEncode($data) {
        return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    }

    // New method to specifically sanitize recipe steps
    public static function sanitizeRecipeStep($step) {
        $step = self::sanitizeInput($step);
        
        // Remove any remaining HTML or script-like content
        $step = preg_replace('/<[^>]*>/', '', $step);
        $step = preg_replace('/\b(javascript|vbscript|expression)\s*:/i', '', $step);
        
        // Remove potential SQL injection patterns
        $step = preg_replace('/\b(union|select|insert|update|delete|drop)\b/i', '', $step);
        
        // Remove multiple spaces and normalize line endings
        $step = preg_replace('/\s+/', ' ', $step);
        $step = str_replace(["\r", "\n"], ' ', $step);
        
        return trim($step);
    }

    // Add new method specifically for recipe names
    public static function validateRecipeName($input) {
        if (empty($input)) {
            throw new Exception("Recipe name cannot be empty");
        }

        // Check length
        if (strlen($input) > 100) {
            throw new Exception("Recipe name is too long");
        }

        // Check for SQL injection patterns
        $sqlPatterns = [
            '/[\'";\-]/',
            '/\b(union|select|insert|update|delete|drop|alter|exec)\b/i',
            '/[\/\\\\]/',
            '/\b(or|and)\b.*?[=<>]/i'
        ];

        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                throw new Exception("Invalid characters in recipe name");
            }
        }

        // Only allow letters, numbers, spaces, and basic punctuation
        if (!preg_match('/^[A-Za-z0-9\s\-.,()]+$/', $input)) {
            throw new Exception("Recipe name contains invalid characters");
        }

        return self::sanitizeInput($input);
    }
}
?> 