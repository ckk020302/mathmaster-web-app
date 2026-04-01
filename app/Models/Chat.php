<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class Chat extends Model
{
    public static function isMathMessage($message)
    {
        // Convert to lowercase for case-insensitive matching
        $message = strtolower(trim($message));
        
        // Math keywords that indicate mathematical questions
        $mathKeywords = [
            'calculate', 'compute', 'solve', 'equation', 'formula',
            'add', 'subtract', 'multiply', 'divide', 'sum', 'difference',
            'product', 'quotient', 'square', 'cube', 'root', 'power',
            'percentage', 'percent', 'fraction', 'decimal', 'ratio',
            'algebra', 'geometry', 'calculus', 'trigonometry', 'statistics',
            'math', 'mathematics', 'arithmetic', 'number', 'integer',
            'derivative', 'integral', 'logarithm', 'exponential',
            'sine', 'cosine', 'tangent', 'angle', 'theorem','total','many',
            'plus', 'minus', 'times', 'count','divided by','mean', 'equals'
        ];
        
        // Check for math keywords
        foreach ($mathKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }
        
        // Enhanced patterns for mathematical expressions
        $mathPatterns = [
            // Basic arithmetic: 2+2, 5-3, 8*4, 10/2
            '/\d+\s*[\+\-\*\/×÷]\s*\d+/',
            
            // Mathematical expressions with equals: 2+2=?, what is 5*3?
            '/\d+\s*[\+\-\*\/×÷]\s*\d+\s*[=?]/',
            
            // Questions about numbers: "what is 5 plus 3", "how much is 2 times 4"
            '/what\s+is\s+\d+\s+(plus|minus|times|divided\s+by|multiplied\s+by)\s+\d+/',
            '/how\s+much\s+is\s+\d+\s*[\+\-\*\/×÷]\s*\d+/',
            
            // Percentage calculations: 20% of 100, what is 15 percent of 200
            '/\d+\s*%\s*of\s*\d+/',
            '/\d+\s*percent\s*of\s*\d+/',
            
            // Square/power operations: 5^2, 3 to the power of 2, square of 4
            '/\d+\s*[\^]\s*\d+/',
            '/\d+\s+to\s+the\s+power\s+of\s+\d+/',
            '/square\s+of\s+\d+/',
            '/cube\s+of\s+\d+/',
            
            // Fraction operations: 1/2 + 1/3, 3/4 * 2/5
            '/\d+\/\d+\s*[\+\-\*\/×÷]\s*\d+\/\d+/',
            
            // Mathematical functions: sin(30), cos(45), log(10)
            '/(sin|cos|tan|log|ln|sqrt)\s*\(\s*\d+\s*\)/',
            
            // Equations: x + 5 = 10, solve for x
            '/[a-z]\s*[\+\-\*\/]\s*\d+\s*=\s*\d+/',
            '/solve\s+for\s+[a-z]/',
            
            // Word problems with numbers and operations
            '/if\s+.*\d+.*[\+\-\*\/].*\d+/',
            '/calculate\s+.*\d+/',
            
            // Mathematical symbols
            '/[√∑∏∫∞π≤≥≠±]/',
        ];
        
        // Check each pattern
        foreach ($mathPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }
        
        // Check for mathematical question structures
        $mathQuestionPatterns = [
            '/what\s+is\s+the\s+(sum|difference|product|quotient)\s+of/',
            '/how\s+do\s+you\s+(add|subtract|multiply|divide)/',
            '/find\s+the\s+(area|volume|perimeter|circumference)/',
            '/what\s+is\s+\d+\s+(squared|cubed)/',
            '/convert\s+.*\d+.*to\s+(decimal|fraction|percentage)/',
        ];
        
        foreach ($mathQuestionPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }
        
        return false;
    }

    public static function sendToGemini($message)
    {
        try {
            $response = Http::withHeaders([
                'x-goog-api-key' => env('GEMINI_API_KEY'),
                'Content-Type'   => 'application/json',
            ])->timeout(15)->retry(2, 200)->post(env('GEMINI_API_ENDPOINT'), [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $message],
                        ],
                    ],
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text']
                       ?? 'No response from Gemini API.';
            }

            return 'The assistant could not answer right now (API error).';
        } catch (\Throwable $e) {
            return 'Network error contacting the assistant.';
        }
    }
}