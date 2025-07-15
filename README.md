# 🏺 Filterer

[![Packagist Version][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
![Github Actions](https://github.com/culturegr/filterer/actions/workflows/run-tests.yml/badge.svg)

This package provides an easy way to add **filtering**, **sorting** and **paging** functionality to Eloquent models.

# Installation

Via [Composer](https://getcomposer.org):

``` bash
$ composer require culturegr/filterer
```

# Usage

Assume the follwing database scheme:

![](https://res.cloudinary.com/culturegr/image/upload/v1596795985/OS%20Packages/Filterer/example_schema_humahz.png)

The `Client` model that corresponds to the `clients` table is shown below:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $date = ['registered_at'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function favoriteProducts()
    {
        return $this->belongsToMany(FavoriteProduct::class);
    }

}
```

> **IMPORTANT** Filterer package strongly relies on Laravel conventions for naming the relationships between models. Therefore, in order for the package to work as expected, the defined **model relationships should be named according to these convantions** 

## Using the Trait

Filtering, sorting and paging functionality can be added to the `Client` model by using the `Filterable` trait provided by Filterer package:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use CultureGr\Filterer\Filterable;

class Client extends Model
{
    use Filterable;
    ...
}
```

## Defining filterable and sortable fields

Fields that can be filtered and/or sorted must be explicitly defined using, respectively, the `fiterable` and `sortable` properties on the model. Both filterable and sortable fields may exist on the model itself or on its first-level relationships:
 - Fields that exist on the model itself are defined using the name of the column, i.e `columnName`
 - Fields that exist on a related model are defined using the relationship name followed by a dot `.` and the name of the column on the related model, i.e `relationshipName.columnName`

For example, in order to be able to both filter and sort the `Client` model by age, country's name, orders' items and favorite products' price, the following filterable and sortable fields must be defined:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use CultureGr\Filterer\Filterable;

class Client extends Model
{
    use Filterable;

    protected $filterable = ['age', 'country.name', 'orders.items', 'favoriteProducts.price'];

    protected $sortable = ['age', 'country.name', 'orders.items', 'favoriteProducts.price'];
    ...
}
```

## Supported data types and operators for filtering

The supported data types and their corresponding operations that can be performed when filtering resources are listed in the following table: 

| Data types                                               | Operators                                                             |
|----------------------------------------------------------|-----------------------------------------------------------------------|
| Numeric (*such as int, tinyint, bigint, float, real etc.*) | equal_to, not_equal_to, less_than, greater_than, between, not_between |
| Date (*such as date, datetime*)                            | between_date                                                         |
| String (*such as varchar, text etc*)                       | equal, not_equal, contains                                            |


## Filtering models

Filtered results can be obtained using the filter method (provided by Filterable trait) and passing an array as an argument that has a filters property which contains a list of desired filters, as shown below:
```php
Client::filter([
    'filter' => [
        [
            'column' => <FILTERABLE>,
            'operator' => <OPERATOR>,
            'query_1' => <VALUE>,
            'query_2' => null|<VALUE> // query_2 is required only with **between**, **not_between** and **between_date** operators
        ],
        ...
    ]
])->get();
```

> **IMPORTANT:** The `filter` method always returns an instance of `Illuminate\Database\Eloquent\Builder`. 

For example, the following filters can be applied in order to filter the clients that
 - have age between 35 and 40 
 - are from Greece
 - had made orders between the 1st and the 31st of May 2020
 - have favorite products that cost less than 10 euros
 
```php
Client::filter([
    'filters' => [
        [
            'column' => 'age',
            'operator' => 'between',
            'query_1' => '35',
            'query_2' => '40'
        ],
        [
            'column' => 'country.name',
            'operator' => 'equal_to',
            'query_1' => 'Greece',
            'query_2' => null
        ],
        [
            'column' => 'orders.shipped_at',
            'operator' => 'between_date',
            'query_1' => '2020-05-1',
            'query_2' => '2020-05-31'
        ],
        [
            'column' => 'favoriteProducts.price',
            'operator' => 'less_than',
            'query_1' => '10',
            'query_2' => null
        ],
    ],
])->get();
```

### Custom filters

Filterer now supports custom filters, allowing you to define complex or specific filtering logic for your models.

#### Creating a Custom Filter

Use the artisan command to generate a new custom filter:

```bash
php artisan make:custom-filter ActiveClientsFilter
```

This will create a new `ActiveClientsFilter` class in `app/CustomFilters` directory that implements the `CustomFilter` interface.
Then you can define your custom filter logic in the `apply` method.

For example, the following `ActiveClientsFilter` will filters clients who have made an order in the last 30 days.:

```php
<?php

namespace App\CustomFilters;

use CultureGr\Filterer\Contracts\CustomFilterInterface;
use Illuminate\Database\Eloquent\Builder;

class ActiveClientsFilter implements CustomFilterInterface
{
    /**
     * Apply the custom filter to the query builder.
     *
     * @param Builder $builder The query builder instance
     * @param array<string, mixed> $filter The filter array
     *
     * @phpstan-param array{
     *      column: string,
     *      operator: string,
     *      query_1: string,
     *      query_2: string
     *  } $filter
     */
    public function apply(Builder $builder, $filter): Builder
    {
        // Your custom filter logic here...

        $queryValue = $filter['query_1']
        
        if ($queryValue === '1') {
            $builder->whereHas('orders', function($q) {
                $q->where('created_at', '>=', Carbon::now()->subDays(30));
            });
        } else {
            $builder->where(function($query) {
                $query->whereDoesntHave('orders')
                    ->orWhereHas('orders', function($q) {
                        $q->where('created_at', '<', Carbon::now()->subDays(30));
                    });
            });
        }
    }
}
```

#### Registering Custom Filters

In your model, add your custom filter to the `$customFilters` array property:

```php
use CultureGr\Filterer\Filterable;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use Filterable;
    
    //...
    
    protected $customFilters = [
        'active' => ActiveClientsFilter::class,
    ];
    
    //...
}
```

#### Using Custom Filters

Now you can use your custom filter in your queries, like you would use any other filter:

```php
Client::filter([
    'filters' => [
        [
            'column' => 'active',
            'operator' => 'equals',
            'query_1' => '1',
            'query_2' => null,
        ],
    ],
])->get();
```

## Sorting models

Sorted results can be obtained using the `filter` method and passing an array as an argument that has a `sorts` property which contains a list of desired sorts, as shown below:

```php
Client::filter([
    'sort' => [
        [
            'column' => <SORTABLE>,
            'direction' => <asc|desc>,
        ],
        ...
    ]
])->get();
```

For example, the following sorts can be applied in order to sort the clients by
 - age, ascending 
 - country name, descending
 - number of items in their orders, ascending 
 - the price of their favorite products, descending
 
```php
Client::filter([
    'sorts' => [
        [
            'column' => 'age',
            'direction' => 'asc',
        ],
        [
            'column' => 'country.name',
            'direction' => 'desc',
        ],
        [
            'column' => 'orders.items',
            'direction' => 'asc',
        ],
        [
            'column' => 'favoriteProducts.price',
            'direction' => 'desc',
        ],
    ]
])->get();
```

## Pagination

The `filterPaginate()` method provides built-in pagination support with automatic page detection and explicit parameter handling.

### Basic Usage

```php
// From URL query parameters (Laravel's automatic page detection)
$clients = Client::filterPaginate(request()->query());

// From stored data like cookies (explicit page handling)
$clients = Client::filterPaginate(['page' => 2, 'limit' => 15, 'filters' => [...]]);

// Custom default limit
$clients = Client::filterPaginate($queryString, 25);
```

### Parameters

| Parameter | Type | Description | Default |
|-----------|------|-------------|---------|
| `limit` | integer | Items per page | 10 |
| `page` | integer | Current page number | Auto-detected or 1 |

### Page Detection Behavior

- **URL parameters**: `?page=2&limit=20` → Laravel's automatic detection works
- **Stored data**: `['page' => 2, 'limit' => 20]` → Explicit handling required
- **Mixed**: URL parameters override stored parameters

### State Persistence Example

```php
public function index()
{
    $filters = request()->query() ?:
               json_decode(request()->cookie('filters'), true) ?:
               [];

    $results = User::filterPaginate($filters);

    return response()->json($results)
        ->cookie('filters', json_encode(request()->query()), 15);
}
```

### Troubleshooting

**Page resets to 1 after refresh?**
Ensure `page` parameter is included in your stored state (cookies, session, etc.).

```php
// ❌ Missing page in stored data
$stored = ['limit' => 20, 'filters' => [...]];

// ✅ Include page in stored data
$stored = ['page' => 2, 'limit' => 20, 'filters' => [...]];
```

## Best Practices

### 1. State Persistence
- Always include both `limit` and `page` parameters in stored state
- Use consistent cookie/session naming conventions
- Set appropriate expiration times for stored filters

### 2. Frontend Integration
- Include pagination parameters in all filter requests
- Handle pagination metadata properly in your UI
- Provide clear feedback when filters are applied

### 3. Performance Considerations
- Use appropriate default limits to balance UX and performance
- Consider using cursor-based pagination for large datasets
- Index filterable columns for better query performance

### 4. Validation
- Validate pagination parameters on the frontend
- Set reasonable min/max limits for pagination
- Handle edge cases (page > total pages, etc.)

## Combining filtering, sorting and paging

Filtering, sorting and paging functionality can be combined using the `filterPaginate` method provided by `Filterable` trait and passing as an argument an array that contains any of the `filters`, `sorts`, `limit`, and `page` properties:

```php
// Using filter (returns Builder)
Client::filter([
    'filters' => [...],
    'sorts' => [...],
])->paginate(); // explicitly call paginate() method on Builder instance

// Using filterPaginate (returns LengthAwarePaginator)
Client::filterPaginate([
    'filters' => [...],
    'sorts' => [...],
    'limit' => 15,
    'page' => 2,
]);
```

## Query string format

The argument of the `filter` method can be easily obtained by parsing a query string with the following format:

```
GET http://example.com/clients
    ?filter[0][column]=<FILTERABLE>&filter[0][operator]=<OPERATOR>&filter[0][query_1]=<VALUE1>&filter[0][query_2]=<VALUE2>
    &filter[1][column]=<FILTERABLE>&filter[0][operator]=in&filter[0][query_1][0]=<VALUE1>&filter[0][query_1][1]=<VALUE2>...
    &filter[2][column]=...
    &sort[0][column]=<SORTABLE>&sort[0][direction]=<DIECTION>
    &sort[1][column]=...
    &limit=<LIMIT>
    &page=<PAGE>
```

# Testing

``` bash
$ composer test
```

# License

Please see the [license file](LICENSE.md) for more information.

# Credits

 - [Code Kerala](https://github.com/codekerala)
 - Awesome Laravel/PHP community

[ico-version]: https://img.shields.io/packagist/v/culturegr/filterer.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/culturegr/filterer.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/culturegr/filterer
[link-downloads]: https://packagist.org/packages/culturegr/filterer
