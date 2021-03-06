A Symfony project created on April 2, 2017, 4:00 pm.

A basic api with symfony 3.2, lexik jwt, nelmio, jms, fosrest
This API allow you to connect and manage user


1. [Prerequisites](#prerequisites)
2. [Setup](#setup)
   * [Installation](#installation)
   * [Database config](#database-config)
   * [Keys generation](#keys-generation)
   * [Config files](#config-files)
   * [FTP server](#ftp-server)
   * [Run](#run)
   * [Routes](#routes)
3. [Test](#test)
   * [Database config for test env](#database-config-for-test-env)
4. [Api Doc](#api-doc)
5. [Connecting a client](#connecting-a-client)
6. [References](#references)

## Prerequisites

- composer
- openssl
- php >= 7.0.0
- ftp

## Setup

#### Installation
```
$> composer install
```

#### Database config
```
$> php bin/console doctrine:database:create --if-not-exists
$> php bin/console doctrine:schema:update --force
$> php bin/console doctrine:fixtures:load
```

#### Keys generation
```
$> mkdir -p var/jwt # For Symfony3+, no need of the -p option
$> openssl genrsa -out var/jwt/private.pem -aes256 4096
$> openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem

```

#### Config files
parameter.yml
```
parameters:
    database_host: 127.0.0.1    #db info
    database_port: 3306         #db info
    database_name: basic-api    #db info
    database_user: root         #db info
    database_password: root     #db info
    mailer_transport: gmail
    mailer_host: 127.0.0.1
    mailer_user: ileotechmail@gmail.com
    mailer_password: d1zqvdzz
    mailer_address: "ileotechmail@gmail.com"
    secret: ThisTokenIsNotSoSecretChangeIt
    jwt_private_key_path: '%kernel.root_dir%/../var/jwt/private.pem'
    jwt_public_key_path: '%kernel.root_dir%/../var/jwt/public.pem'
    jwt_key_pass_phrase: basic-api
    jwt_token_ttl: 3600
    basic_api.ftp_path: 127.0.0.1       #ftp_info
    basic_api.ftp_login: login       #ftp_info
    basic_api.ftp_password: password    #ftp_info
```

#### FTP server

If you want to use ftp function like avatar upload you should launch a ftp server.

For launching it locally

`$> ftp localhost `

Then for login : a user from your computer and his password.

And change the parameter file consequently

#### Run

`$> php bin/console server:run`

#### Routes

`$> ./bin/console debug:router`

## Test

Unit test are running with phpunit, you can find a php7 executable of phpunit in bin/

#### Database config for test env
```
$> php bin/console doctrine:database:create --env=test --if-not-exists
$> php bin/console doctrine:schema:update --force --env=test
$> php bin/console doctrine:fixtures:load --env=test
```

`$> php bin/phpunit`

## API DOC

The documentation is generated using the [NelmioApiDocBundle](https://github.com/nelmio/NelmioApiDocBundle).
Access the documentation at http://localhost:8000/api/doc

## Connecting a client

You can connect to the api by POST on http://localhost:8000/api/auth the parameters are : [username => your_login, password => your_password]
(two based user are already in database in ORM fixtures, with username:ileo-admin password:admin and username:ileo-user password:user)

For further details, read the documentation about [JsonWebToken](https://jwt.io/introduction/).

Read your api doc to learn what you can do with the API

## References

* [Symfony](https://symfony.com/)
* [FOSRestBundle](http://symfony.com/doc/master/bundles/FOSRestBundle/index.html)
* [NelmioApiDocBundle](https://github.com/nelmio/NelmioApiDocBundle)
* [JMSSerializerBundle](http://jmsyst.com/bundles/JMSSerializerBundle)
* [LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle)
* [NelmioCorsBundle](https://github.com/nelmio/NelmioCorsBundle)
