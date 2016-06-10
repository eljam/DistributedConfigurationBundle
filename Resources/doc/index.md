# MaikuroDistributedConfigurationBundle

# Installation

```bash
$ composer.phar require gundan/distributed-configuration-bundle
```

Register the bundle in `app/AppKernel.php`:

``` php
// app/AppKernel.php
public function registerBundles()
{
    return array(
        // ...
        new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
        new \JMS\SerializerBundle\JMSSerializerBundle($this),
        new \FOS\RestBundle\FOSRestBundle()
        new \Maikuro\DistributedConfigurationBundle\MaikuroDistributedConfigurationBundle(),
    );
}
```

Enable the bundle's configuration in `app/config/config.yml`:

``` yaml
# app/config/config.yml
maikuro_distributed_configuration:
    store:
        json:
            path: "%kernel.root_dir%/test.json"
```

Configure your routing file in `app/config/routing.yml`:

``` yaml
# app/config/routing.yml
maikuro_distributed_configuration_api:
    resource: "@MaikuroDistributedConfigurationBundle/Resources/config/routing.yml"
    prefix: /v1

fos_rest:
    exception:
        codes:
            'Webmozart\KeyValueStore\Api\WriteException': 400 #translate write exception to 400 error code
            'Webmozart\KeyValueStore\Api\NoSuchKeyException': 404 #translate no found key exception to 404 error code
    format_listener:
        rules:
            - { path: '^/v1/', priorities: ['json'], fallback_format: json, prefer_extension: false }
```

## Usage

Use your favorite http client

```php
$response = $client->request('POST','/v1/keys', ['body' => json_encode(['client_uri' => 'http://api.example.org'])]);
```

## Api

| Url                 | Method      |  Comment
| --------------------|:-----------:|:-------------:
| /v1/keys            | GET        | All keys
| /v1/keys            | POST        | Create a key value
| /v1/keys/{key_name} | GET         | Get a value from a key
| /v1/keys/{key_name} | PUT         | Modify a value for a specified a key (you must provide the key in the json data)
| /v1/keys/{key_name} | PATCH       | Modify a value for a specified key
| /v1/keys/{key_name} | DELETE      | Delete a key
