# Slim API Skeleton

This is a PHP API skeleton that is based on the [Slim 3 micro framework](http://www.slimframework.com) to help getting started in building REST application.

## Dependencies

The project uses the following packages

- [illuminate/database](https://github.com/illuminate/database) which serves as the database layer of the application
- [firebase/php-jwt](https://github.com/firebase/php-jwt) to manage JSON web tokens and for JWT Authentication
- [respect/validation](https://github.com/respect/validation) for validation
- [monolog/monolog](https://github.com/Seldaek/monolog) for logging
- [spatie/fractalistic](https://github.com/thephpleague/fractal) which is wrapper for [ThePhpLeague Fractal](https://github.com/thephpleague/fractal) which provides presentation and transformation layer data output
- [robmorgan/phinx](https://github.com/cakephp/phinx) to manage database migrations
- [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) to load environment variables stored in `.env` file
- [fzaninotto/faker](https://github.com/fzaninotto/faker) for generating faker data

## Installation

- Run `git clone https://github.com/kennedy-osaze/slim-api-skeleton.git` to clone the repository
- Run `composer install` from the application root folder
- From the project root run `cp .env.example .env`
- Ensure `storage/` is web writeable.
