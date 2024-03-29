# Set of classes for creating CRUD services
[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/mezon-router) [![Build Status](https://travis-ci.com/alexdodonov/mezon-crud-service.svg?branch=master)](https://travis-ci.com/alexdodonov/mezon-crud-service) [![codecov](https://codecov.io/gh/alexdodonov/mezon-crud-service/branch/master/graph/badge.svg)](https://codecov.io/gh/alexdodonov/mezon-crud-service)

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

## Default endpoints

Out of the box a list of CRUD endpoints are available:

```PHP
GET /list/
GET /all/ 
GET /exact/list/[il:ids]/
GET /exact/[i:id]/
GET /fields/
POST|PUT /delete/[i:id]/
POST|DELETE  /delete/
POST|PUT /create/
POST /update/[i:id]/
GET /new/from/[s:date]/
GET /records/count/
GET /last/[i:count]/
GET /records/count/[s:field]
```
