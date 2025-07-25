<?php

if (!function_exists('array_to_gql')) {

    /**
     * Convert a PHP array to GraphQL query syntax
     * 
     * This function transforms a PHP array structure into a valid GraphQL query string.
     * It supports nested structures, parameters, aliases, and various value types.
     * 
     * Special array keys:
     * - '__args': Define query parameters/arguments
     * - '__aliasFor': Create field aliases
     * 
     * Value processing rules:
     * - true: Shows field name only
     * - false: Field is ignored/excluded
     * - strings/numbers: Shows field name only (value ignored)
     * - arrays: Recursively processed as nested structures
     * 
     * @param array $array The PHP array to convert to GraphQL syntax
     * @return string The generated GraphQL query string
     */
    function array_to_gql($array): string
    {
        $gql = '';
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                // 檢查是否有別名功能
                $aliasFor = isset($value['__aliasFor']) ? $value['__aliasFor'] : null;
                $actualKey = $aliasFor ? $aliasFor : $key;
                $aliasPrefix = $aliasFor ? "{$key}: " : "";
                
                // 檢查是否有 __args 參數
                if (isset($value['__args'])) {
                    $args = $value['__args'];
                    $argString = '';
                    
                    // 建構參數字串
                    foreach ($args as $argKey => $argValue) {
                        if ($argString !== '') {
                            $argString .= ', ';
                        }
                        
                        $argString .= "{$argKey}: " . formatArgumentValue($argValue);
                    }
                    
                    // 移除特殊鍵以便處理其他字段
                    unset($value['__args']);
                    if ($aliasFor) {
                        unset($value['__aliasFor']);
                    }
                    
                    // 檢查是否還有其他字段需要查詢
                    $remainingFields = array_to_gql($value);
                    if (trim($remainingFields) !== '') {
                        // 生成帶參數和字段的查詢
                        $gql .= "{$aliasPrefix}{$actualKey}({$argString}) { {$remainingFields} } ";
                    } else {
                        // 只有參數，沒有字段
                        $gql .= "{$aliasPrefix}{$actualKey}({$argString}) ";
                    }
                } else {
                    // 移除特殊鍵以便處理其他字段
                    if ($aliasFor) {
                        unset($value['__aliasFor']);
                    }
                    
                    $gql .= "{$aliasPrefix}{$actualKey} { " . array_to_gql($value) . " } ";
                }
            } else {
                if ($value === false) {
                    // 如果值為 false，跳過此字段（不輸出）
                    continue;
                } else {
                    // 對於其他值（true、字符串、數字等），只顯示鍵名
                    $gql .= "{$key} ";
                }
            }
        }
        return trim($gql);
    }

    /**
     * Format a value for use as a GraphQL argument
     * 
     * This helper function properly formats different value types for use in GraphQL arguments.
     * It handles arrays (converted to lists or objects) and uses json_encode for scalar values
     * to ensure proper type handling and escaping.
     * 
     * Type handling rules:
     * - Arrays: Converted to GraphQL lists [] or objects {}
     * - Scalar values: Handled by json_encode() for proper type preservation
     *   - Boolean true/false: Literal true/false (no quotes)
     *   - Integer/Float: Literal numbers (no quotes)
     *   - Strings (including numeric strings): Quoted strings with proper escaping
     * 
     * @param mixed $value The value to format
     * @return string The formatted value ready for GraphQL argument usage
     * 
     * @example
     * formatArgumentValue(true)           // Returns: true
     * formatArgumentValue(123)            // Returns: 123
     * formatArgumentValue(99.99)          // Returns: 99.99
     * formatArgumentValue("123")          // Returns: "123"
     * formatArgumentValue("hello")        // Returns: "hello"
     * formatArgumentValue(['A', 'B'])     // Returns: ["A", "B"]
     * formatArgumentValue(['key' => 'val']) // Returns: {key: "val"}
     */
    function formatArgumentValue($value) {
        if (is_array($value)) {
            // 如果參數值是數組
            if (empty($value)) {
                return '{}';
            }
            
            // 檢查是否為索引數組（列表）
            if (array_keys($value) === range(0, count($value) - 1)) {
                // 索引數組 - 生成列表格式 [item1, item2, ...]
                $listString = '[';
                $first = true;
                foreach ($value as $item) {
                    if (!$first) {
                        $listString .= ', ';
                    }
                    $listString .= formatArgumentValue($item);
                    $first = false;
                }
                $listString .= ']';
                return $listString;
            } else {
                // 關聯數組 - 生成對象格式 {key: value, ...}
                $objString = '{';
                $first = true;
                foreach ($value as $objKey => $objValue) {
                    if (!$first) {
                        $objString .= ', ';
                    }
                    $objString .= "{$objKey}: " . formatArgumentValue($objValue);
                    $first = false;
                }
                $objString .= '}';
                return $objString;
            }
        } else {
            // 對於非數組值，使用 json_encode 來處理類型和轉義
            // JSON_UNESCAPED_UNICODE 確保 Unicode 字符正確顯示
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
    }
}
