<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../function.php';

class ArrayToGqlTest extends TestCase
{
    /**
     * 測試基本功能 - 簡單字段
     */
    public function testBasicFields(): void
    {
        $input = [
            'user' => [
                'id' => 1,
                'name' => 'John Doe'
            ]
        ];
        
        $expected = 'user { id name }';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試嵌套數組
     */
    public function testNestedArrays(): void
    {
        $input = [
            'user' => [
                'id' => 1,
                'name' => 'John Doe',
                'posts' => [
                    'title' => 'Hello World',
                    'content' => 'This is a test post.'
                ]
            ]
        ];
        
        $expected = 'user { id name posts { title content } }';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試布爾值字段
     */
    public function testBooleanFields(): void
    {
        $input = [
            'users' => [
                'first_name' => true,
                'last_name' => true,
                'email' => true
            ]
        ];
        
        $expected = 'users { first_name last_name email }';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試簡單參數
     */
    public function testSimpleArguments(): void
    {
        $input = [
            'users' => [
                '__args' => [
                    'search' => 'a'
                ],
                'first_name' => true,
                'last_name' => true
            ]
        ];
        
        $expected = 'users(search: "a") { first_name last_name }';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試多個參數
     */
    public function testMultipleArguments(): void
    {
        $input = [
            'posts' => [
                '__args' => [
                    'limit' => '10',
                    'status' => 'published'
                ],
                'id' => true,
                'title' => true
            ]
        ];
        
        $expected = 'posts(limit: "10", status: "published") { id title }';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試對象參數
     */
    public function testObjectArguments(): void
    {
        $input = [
            'users' => [
                '__args' => [
                    'search' => [
                        'first_name' => 'a',
                        'last_name' => 'b'
                    ]
                ],
                'first_name' => true,
                'last_name' => true
            ]
        ];
        
        $expected = 'users(search: {first_name: "a", last_name: "b"}) { first_name last_name }';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試別名功能
     */
    public function testAlias(): void
    {
        $input = [
            'allUser' => [
                '__aliasFor' => 'users',
                'first_name' => true,
                'last_name' => true
            ]
        ];
        
        $expected = 'allUser: users { first_name last_name }';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試別名與參數組合
     */
    public function testAliasWithArguments(): void
    {
        $input = [
            'allUser' => [
                '__aliasFor' => 'users',
                '__args' => [
                    'search' => [
                        'first_name' => 'a',
                        'last_name' => 'b'
                    ]
                ],
                'first_name' => true,
                'last_name' => true
            ]
        ];
        
        $expected = 'allUser: users(search: {first_name: "a", last_name: "b"}) { first_name last_name }';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試複雜嵌套結構
     */
    public function testComplexNestedStructure(): void
    {
        $input = [
            'posts' => [
                '__args' => [
                    'limit' => '10',
                    'status' => 'published'
                ],
                'id' => true,
                'title' => true,
                'author' => [
                    'name' => true,
                    'email' => true,
                    'profile' => [
                        'bio' => true,
                        'avatar' => true
                    ]
                ]
            ]
        ];
        
        $expected = 'posts(limit: "10", status: "published") { id title author { name email profile { bio avatar } } }';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試空數組
     */
    public function testEmptyArray(): void
    {
        $input = [];
        
        $expected = '';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試混合類型值
     */
    public function testMixedValueTypes(): void
    {
        $input = [
            'query' => [
                'stringField' => 'test',
                'numberField' => 123,
                'booleanField' => true,
                'nestedField' => [
                    'subField' => true
                ]
            ]
        ];
        
        $expected = 'query { stringField numberField booleanField nestedField { subField } }';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試 false 值被忽略
     */
    public function testFalseValuesAreIgnored(): void
    {
        $input = [
            'users' => [
                'first_name' => true,
                'last_name' => false,
                'email' => true,
                'phone' => false
            ]
        ];
        
        $expected = 'users { first_name email }';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試嵌套結構中的 false 值
     */
    public function testFalseValuesInNestedStructure(): void
    {
        $input = [
            'user' => [
                'id' => true,
                'profile' => [
                    'name' => true,
                    'bio' => false,
                    'avatar' => true,
                    'settings' => [
                        'theme' => false,
                        'notifications' => true
                    ]
                ],
                'posts' => false
            ]
        ];
        
        $expected = 'user { id profile { name avatar settings { notifications } } }';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試字符串和數字值只顯示鍵名
     */
    public function testStringAndNumberValuesShowOnlyKeys(): void
    {
        $input = [
            'users' => [
                'name' => [
                    'first' => 'John',
                    'last' => 'Doe'
                ],
                'age' => 25,
                'status' => 'active',
                'email' => true,
                'phone' => false
            ]
        ];
        
        $expected = 'users { name { first last } age status email }';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }
}
