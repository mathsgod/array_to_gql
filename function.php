<?php

if (!function_exists('array_to_gql')) {

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
                    
                    // 生成帶參數的查詢
                    $gql .= "{$aliasPrefix}{$actualKey}({$argString}) { " . array_to_gql($value) . " } ";
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

    function formatArgumentValue($value) {
        if (is_array($value)) {
            // 如果參數值是數組，建構對象格式（支持多層嵌套）
            if (empty($value)) {
                return '{}';
            }
            
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
        } elseif (is_bool($value)) {
            // 布爾值直接返回 true 或 false，不加引號
            return $value ? 'true' : 'false';
        } else {
            return "\"" . addslashes($value) . "\"";
        }
    }
}
