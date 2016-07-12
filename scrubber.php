<?php

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

require 'vendor/autoload.php';

$faker = Faker\Factory::create('nl_NL');
$config = new Configuration();
$connectionParams = array(
    'dbname' => 'scrubber',
    'user' => 'root',
    'password' => 'vagrant',
    'host' => '192.168.99.100',
    'driver' => 'pdo_mysql',
);
$conn = DriverManager::getConnection($connectionParams, $config);

// Custom obfuscators
$obfusticators = [
    'wbFirstName' => function (array $v) use ($faker) {
        if ($v['firstName'] !== null) {
            return $faker->firstName($v['gender'] === 'm' ? 'male' : 'female');
        }
    },
];

// Configuration
$tableConfig = [
    'tables' => [
        [
            'table' => 'User',
            'identifier' => 'id',
            'columns' => [
                [
                    'name' => 'firstName',
                    // This matches the custom obfusticator
                    'obfusticator' => 'wbFirstName'
                ],
                [
                    'name' => 'lastName',
                    // This will match the lastName from Faker
                    'obfusticator' => 'lastName'
                ],
                [
                    'name' => 'email',
                    'obfusticator' => 'email'
                ]
            ]
        ]
    ]

];

foreach ($tableConfig['tables'] as $table) {
    $stmt = $conn->prepare(sprintf('SELECT * FROM %s', $table['table']));
    $stmt->execute();

    while ($user = $stmt->fetch()) {
        print_r($user);
        foreach ($table['columns'] as $column) {
            if (isset($obfusticators[$column['obfusticator']])) {
                $user[$column['name']] = $obfusticators[$column['obfusticator']]($user);
            } else {
                $user[$column['name']] = $faker->{$column['obfusticator']}();
            }

            $conn->update($table['table'], $user, [
                $table['identifier'] => $user[$table['identifier']]
            ]);
        }
        print_r($user);
        echo PHP_EOL;
        echo PHP_EOL;
    }
}
