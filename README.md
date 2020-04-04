# Magento 2 Bugsnag Notifier

![phpcs](https://github.com/ashsmith/magento-bugsnag-notifier-module/workflows/phpcs/badge.svg)

This module integrates the [Bugsnag](https://www.bugsnag.com) notifier into Magento's exception handling. Any exception that is left unhandled and eventually caught within the `Magento\Framework\App\Bootstrap::run` method will then be notified to Bugsnag.

This works by using two plugins (interceptors) that are placed before the `launch` and `catchException` methods on any class that implement the `Magento\Framework\AppInterface` interface. Within the `beforeLaunch` plugin Bugsnag is initialised. You can optionally start a session which enables Bugsnag's [stability score](https://www.bugsnag.com/product/stability-score) (requires their Standard package).


## Installation

    composer require ashsmith/magento-bugsnag-notifier-module ^1.0.0
    bin/magento setup:upgrade

## Configuration

To configure the Bugsnag Notifier you have two options:

1) Use the following environment variables:

- `BUGSNAG_ENABLED=1` 1 or 0 to enable/disable.
- `BUGSNAG_API_KEY=[your api key]`
- `BUGSNAG_ENDPOINT=[custom host if required]` (optional)
- `BUGSNAG_RELEASE_STAGE=[your environment/release stage (production/staging etc)`
- `BUGSNAG_SESSION_TRACKING=enabled` (Optional. Requires Bugsnag Standard package)

2) Add the following configuration to: `app/etc/env.php`

```php
return [
    ...
    'bugsnag' => [
        'enabled' => '1',
        'api_key' => 'YOUR API KEY',
        'endpoint' => 'custom endpoint if required',
        'release_stage' => 'production',
        'session_tracking' => 'enabled',
    ]
]
```


## Extending Bugsnag Metadata

If you wish to send additional data to Bugsnag that may help debug your Magento application, there are two events dispatched by this module which can be used to add additional metadata.

### Event: `bugsnag_init`
`bugsnag_init` is dispatched when the module first initialises Bugsnag and a session is started. You'll be able to retrieve the Bugsnag client by doing: `$observer->getData('client')` which will give you an instance of `\Bugsnag\Client`. From there adding metadata can be done like so:

```php
/** @var \Bugsnag\Client $client */
$client = $observer->getData('client');
$client->setMetaData([
    'app' => [
        'new_property' => 'new_value'
    ]
]);
```

Alternatively you can register a callback which will be executed when an exception occurs to add additional information to the report:

```php
/** @var \Bugsnag\Client $client */
$client = $observer->getData('client');
$client->registerCallback(function (\Bugsnag\Report $report) {
    $report->setMetaData([
        'app' => [
            'new_property' => 'new_value'
        ]
    ]);
});
```

### Event: `bugsnag_add_customer_data`
This event allows you add additional customer data to the exception report, you can fetch the current customer data object by calling: `$observer->getData('data')`.

This is an instance of `\Magento\Framework\DataObject`, so you can use the `setData` and `getData` properties to modify the object.

By default the module will only send the customers: ID, customer group, and a boolean whether or not they are logged in.