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
        
        $expected = 'searchUsers: users(filter: {age: {min: 18, max: 65}, location: {country: "US", city: "New York"}}, limit: 100) { id profile { name contacts { email phone } } }';
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
        
        $expected = 'search(query: "hello \"world\"", limit: 10, offset: 0) { results { id title } }';
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
        
        $expected = 'mutation { deleteUser(id: 123) }';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試索引數組參數（列表）
     */
    public function testIndexedArrayArguments(): void
    {
        $input = [
            'addUser' => [
                '__args' => [
                    'tags' => ['php', 'graphql', 'array'],
                    'numbers' => [1, 2, 3]
                ]
            ]
        ];
        
        $expected = 'addUser(tags: ["php", "graphql", "array"], numbers: [1, 2, 3])';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試嵌套的索引數組和關聯數組
     */
    public function testNestedArrayArguments(): void
    {
        $input = [
            'addUser' => [
                '__args' => [
                    'data' => [
                        'name' => 'John',
                        'tags' => ['admin', 'user'],
                        'permissions' => [
                            'read' => true,
                            'roles' => ['editor', 'viewer']
                        ]
                    ]
                ]
            ]
        ];
        
        $expected = 'addUser(data: {name: "John", tags: ["admin", "user"], permissions: {read: true, roles: ["editor", "viewer"]}})';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試數字類型參數
     */
    public function testNumericArguments(): void
    {
        $input = [
            'mutation' => [
                'updateProduct' => [
                    '__args' => [
                        'id' => 123,           // 真實整數
                        'price' => 99.99,      // 真實浮點數
                        'discount' => 0.15,    // 真實浮點數
                        'sku' => '456'         // 數字字符串（保持字符串）
                    ]
                ]
            ]
        ];
        
        $expected = 'mutation { updateProduct(id: 123, price: 99.99, discount: 0.15, sku: "456") }';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試混合類型參數（數字、布爾、字符串、數組）
     */
    public function testMixedTypeArguments(): void
    {
        $input = [
            'addProduct' => [
                '__args' => [
                    'id' => 789,
                    'name' => 'Product Name',
                    'active' => true,
                    'price' => 29.95,
                    'tags' => ['sale', 'new'],
                    'metadata' => [
                        'weight' => 1.5,
                        'available' => false
                    ]
                ]
            ]
        ];
        
        $expected = 'addProduct(id: 789, name: "Product Name", active: true, price: 29.95, tags: ["sale", "new"], metadata: {weight: 1.5, available: false})';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * 測試各種數據類型的正確處理
     */
    public function testVariousDataTypes(): void
    {
        $input = [
            'mutation' => [
                'createUser' => [
                    '__args' => [
                        'realInt' => 123,           // 真實整數
                        'realFloat' => 99.99,       // 真實浮點數
                        'stringInt' => '456',       // 數字字符串（保持字符串）
                        'stringFloat' => '78.9',    // 浮點數字符串（保持字符串）
                        'name' => 'John',           // 常規字符串
                        'active' => true,           // 布爾值
                        'settings' => ['theme' => 'dark'], // 對象
                        'tags' => ['admin', 'user'] // 數組
                    ]
                ]
            ]
        ];
        
        $expected = 'mutation { createUser(realInt: 123, realFloat: 99.99, stringInt: "456", stringFloat: "78.9", name: "John", active: true, settings: {theme: "dark"}, tags: ["admin", "user"]) }';
        $result = array_to_gql($input);
        
        $this->assertEquals($expected, $result);
    }
}
