<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../function.php';

class ArrayToGqlEdgeCasesTest extends TestCase
{
    /**
     * 測試複雜對象參數與多層嵌套
     */
    public function testComplexObjectArgumentsWithNesting(): void
    {
        $input = [
            'searchUsers' => [
                '__aliasFor' => 'users',
                '__args' => [
                    'filter' => [
                        'age' => [
                            'min' => 18,
                            'max' => 65
                        ],
                        'location' => [
                            'country' => 'US',
                            'city' => 'New York'
                        ]
                    ],
                    'limit' => 100
                ],
                'id' => true,
                'profile' => [
                    'name' => true,
                    'contacts' => [
                        'email' => true,
                        'phone' => true
                    ]
                ]
            ]
        ];
        
        $expected = 'searchUsers: users(filter: {age: {min: "18", max: "65"}, location: {country: "US", city: "New York"}}, limit: "100") { id profile { name contacts { email phone } } }';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試多個別名查詢
     */
    public function testMultipleAliases(): void
    {
        $input = [
            'activeUsers' => [
                '__aliasFor' => 'users',
                '__args' => [
                    'status' => 'active'
                ],
                'id' => true,
                'name' => true
            ],
            'inactiveUsers' => [
                '__aliasFor' => 'users',
                '__args' => [
                    'status' => 'inactive'
                ],
                'id' => true,
                'name' => true
            ]
        ];
        
        $expected = 'activeUsers: users(status: "active") { id name } inactiveUsers: users(status: "inactive") { id name }';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試只有別名沒有參數
     */
    public function testAliasWithoutArguments(): void
    {
        $input = [
            'allUsers' => [
                '__aliasFor' => 'users',
                'id' => true,
                'name' => true,
                'posts' => [
                    'title' => true,
                    'content' => true
                ]
            ]
        ];
        
        $expected = 'allUsers: users { id name posts { title content } }';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試特殊字符和數字值
     */
    public function testSpecialCharactersAndNumbers(): void
    {
        $input = [
            'search' => [
                '__args' => [
                    'query' => 'hello "world"',
                    'limit' => 10,
                    'offset' => 0
                ],
                'results' => [
                    'id' => true,
                    'title' => true
                ]
            ]
        ];
        
        $expected = 'search(query: "hello \"world\"", limit: "10", offset: "0") { results { id title } }';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試空對象參數
     */
    public function testEmptyObjectArguments(): void
    {
        $input = [
            'users' => [
                '__args' => [
                    'filter' => []
                ],
                'id' => true
            ]
        ];
        
        $expected = 'users(filter: {}) { id }';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試參數中的布爾值
     */
    public function testBooleanValuesInArguments(): void
    {
        $input = [
            'mutation' => [
                'addUser' => [
                    '__args' => [
                        'data' => [
                            'name' => 'John',
                            'is_active' => true,
                            'is_verified' => false,
                            'metadata' => [
                                'premium' => true,
                                'trial' => false
                            ]
                        ]
                    ],
                    'id' => true,
                    'name' => true
                ]
            ]
        ];
        
        $expected = 'mutation { addUser(data: {name: "John", is_active: true, is_verified: false, metadata: {premium: true, trial: false}}) { id name } }';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試只有參數沒有字段的情況
     */
    public function testArgumentsOnlyWithoutFields(): void
    {
        $input = [
            'mutation' => [
                'deleteUser' => [
                    '__args' => [
                        'id' => 123
                    ]
                ]
            ]
        ];
        
        $expected = 'mutation { deleteUser(id: "123") }';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }
}
