A Symfony project created on April 2, 2017, 4:00 pm.

A basic api with symfony 3.2, lexik jwt, nelmio, jms, fosrest 

## Prerequisites

- composer
- openssl

## Setup

```
$> composer install
$> php bin/console doctrine:database:create --if-not-exists
$> php bin/console doctrine:schema:update --force
$> php bin/console doctrine:fixtures:load
$> mkdir -p var/jwt # For Symfony3+, no need of the -p option
$> openssl genrsa -out var/jwt/private.pem -aes256 4096
$> openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem
$> php bin/console server:run

```

### Run

`$> php bin/console server:run`


### Routes

`$> ./bin/console route:debug`

## API DOC

The documentation is generated using the [NelmioApiDocBundle](https://github.com/nelmio/NelmioApiDocBundle).
Access the documentation at http://localhost:8000/api/doc


## References

* [Symfony](https://symfony.com/)
* [FOSRestBundle](http://symfony.com/doc/master/bundles/FOSRestBundle/index.html)
* [NelmioApiDocBundle](https://github.com/nelmio/NelmioApiDocBundle)
* [JMSSerializerBundle](http://jmsyst.com/bundles/JMSSerializerBundle)
* [LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle)
