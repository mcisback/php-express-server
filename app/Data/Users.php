<?php

namespace App\Data;

class Users {
    public static function getAll() {
        return [
            [
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'age' => 30,
                'city' => 'New York',
            ],
            [
                'id' => 2,
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'age' => 25,
                'city' => 'Los Angeles',
            ],
            [
                'id' => 3,
                'name' => 'Michael Johnson',
                'email' => 'michael@example.com',
                'age' => 40,
                'city' => 'Chicago',
            ],
            [
                'id' => 4,
                'name' => 'Emily Davis',
                'email' => 'emily@example.com',
                'age' => 28,
                'city' => 'San Francisco',
            ],
            [
                'id' => 5,
                'name' => 'William Brown',
                'email' => 'william@example.com',
                'age' => 35,
                'city' => 'Boston',
            ],
            [
                'id' => 6,
                'name' => 'Emma Wilson',
                'email' => 'emma@example.com',
                'age' => 27,
                'city' => 'Seattle',
            ],
            [
                'id' => 7,
                'name' => 'Daniel Martinez',
                'email' => 'daniel@example.com',
                'age' => 33,
                'city' => 'Houston',
            ],
            [
                'id' => 8,
                'name' => 'Olivia Taylor',
                'email' => 'olivia@example.com',
                'age' => 29,
                'city' => 'Miami',
            ],
            [
                'id' => 9,
                'name' => 'Alexander Anderson',
                'email' => 'alexander@example.com',
                'age' => 32,
                'city' => 'Denver',
            ],
            [
                'id' => 10,
                'name' => 'Sophia Thomas',
                'email' => 'sophia@example.com',
                'age' => 26,
                'city' => 'Philadelphia',
            ],
        ];
    }
}