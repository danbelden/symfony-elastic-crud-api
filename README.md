# danbelden/symfony-elastic-crud-api

[![CircleCI](https://circleci.com/gh/danbelden/symfony-elastic-crud-api.svg?style=svg)](https://circleci.com/gh/danbelden/symfony-elastic-crud-api)

This is a symfony REST API for accessing JSON documents `models` store in Elastic Search.

This project is has a dev environment enabled by docker for rapid-development.

For ease it also circulates with a `Makefile` with helper commands for support:

```
$ make
help                           Show this help message.
setup                          Run project fresh local dev install
start                          Start the project dev environment
stop                           Stop the project dev environment
logs                           Tail stream all dev container logs
test                           Run project tests
```

## Setup

To initialise and build/setup the dev environment simply type `make setup`.

This will perform the following steps:

- Stop any running containers managed by `docker-compose`
- Clear any pre-existing Symfony temp files
- Install project dependencies using composer
- Build any docker images required for the dev environment

The command will look something like this once launched:

```
$ make setup
Loading composer repositories with package information
Updating dependencies (including require-dev)
Package operations: 0 installs, 4 updates, 0 removals
  - Updating twig/twig (v2.4.6 => v2.4.7): Downloading (100%)
  - Updating friendsofphp/php-cs-fixer (v2.10.4 => v2.11.1): Downloading (100%)
...
```

## Start

To launch the docker local dev environment simply type `make start`.

The command will look something like this once launched:

```
$ make start
Creating network "symfonyelasticcrudapi_default" with the default driver
Creating symfonyelasticcrudapi_api_1     ... done
Creating symfonyelasticcrudapi_elastic_1 ... done
```

You can confirm both containers are running using `docker ps -q`:

```
$ docker ps -q
ae073a892732
befb4224272e
```

## Stop

To stop the docker local dev environment simply type `make stop`.

The command will look something like this once launched:

```
$ make stop
Stopping symfonyelasticcrudapi_api_1     ... done
Stopping symfonyelasticcrudapi_elastic_1 ... done
```

Note: If you run this command when no containers are running, nothing will output:

```
$ make stop
$
```

## Logs

Sometimes when you are developing it's useful to access the FPM and NGINX logs for debugging.

To access these logs you have two options, connect to the container manually...

OR, use the development command `make logs` which will tail stream the running container logs for you.

The command will look something like this once launched:

```
$ make logs
Attaching to symfonyelasticcrudapi_api_1, symfonyelasticcrudapi_elastic_1
api_1      | Starting php-fpm  done
elastic_1  | [2018-03-30T14:45:36,349][INFO ][o.e.n.Node               ] [] initializing ...
...
```

Note: To exit the logs stream simply press [cntrl] + [c] at the same time!

## Test

Finally, it is wise to develop software with automated tests and inline with code style checks.

This project is configured with `phpunit` and `php-cs-fixer` coverage.

- https://github.com/sebastianbergmann/phpunit
- https://github.com/FriendsOfPHP/PHP-CS-Fixer

To run these tools simply type `make test`, the output should look like this:

```
$ make test
Loaded config default from ".php_cs.dist".
................................................................................
Legend: ?-unknown, I-invalid file syntax, file ignored, S-Skipped, .-no changes, F-fixed, E-error

Checked all files in 2.152 seconds, 10.000 MB memory used
PHPUnit 7.0.3 by Sebastian Bergmann and contributors.

...............................................................  63 / 142 ( 44%)
............................................................... 126 / 142 ( 88%)
................                                                142 / 142 (100%)

Time: 34.01 seconds, Memory: 36.00MB

OK (142 tests, 377 assertions)
```

These tools are run using docker containers to avoid local environment issues.

This can come at a minor cost of performance to initialise the containers for each tool execution.

## Api docs

This application comes with self-generating `swagger` api documentation built in.

<img src="https://raw.githubusercontent.com/danbelden/symfony-elastic-crud-api/master/docs/img/swagger-api-doc-min.png" width="600" />

This can be accessed once the dev environment is running on the url:
- http://localhost/doc

It is re-generated from controller annotations every time you refresh the `/doc` webpage.
- https://github.com/danbelden/symfony-elastic-crud-api/blob/master/src/AppBundle/Controller/CreateController.php#L9
- https://github.com/danbelden/symfony-elastic-crud-api/blob/master/src/AppBundle/Controller/CreateController.php#L15-L41

## Summary

There are various useful features to this project, please feel free to fork and leverage it in your app.

Enjoy.
