# Set of classes for creating CRUD services [![Build Status](https://travis-ci.com/alexdodonov/mezon-crud-service.svg?branch=master)](https://travis-ci.com/alexdodonov/mezon-crud-service) [![codecov](https://codecov.io/gh/alexdodonov/mezon-crud-service/branch/master/graph/badge.svg)](https://codecov.io/gh/alexdodonov/mezon-crud-service)

## Installation

Just print in console

```
composer require mezon/crud-service
```

And that's all )

## First steps

Now we are ready to create out first CRUD service. Here it is:

```PHP

/**
 * Service class
 */
class TodoService extends \Mezon\CrudService\CrudService
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct([
            'fields' => [
                'id' => [
                    'type' => 'integer'
                ],
                'title' => [
                    'type' => 'string'
                ]
            ],
            'table-name' => 'records',
            'entity-name' => 'record'
        ]);
    }
}

$service = new TodoService();
$service->run();
```

The main part of this listing is:

```PHP
parent::__construct([
	'fields' => [
		'id' => [
			'type' => 'integer'
		],
		'title' => [
			'type' => 'string'
		]
	],
	'table-name' => 'records',
	'entity-name' => 'record'
]);
```

Here we describe a list of fields of our entity, table name where it is stored and entity name.