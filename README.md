Silex WSSE
==========

Provides a Silex provider in order to implement
a [WSSE authentication](http://silex.sensiolabs.org/doc/providers/security.html#defining-a-custom-authentication-provider).


## Installation

Via Composer

``` js
{
    "require": {
        "alcalyn/silex-wsse": "~1.0.0"
    }
}
```


## Usage

``` php
use Alcalyn\Wsse\Security\Authentication\Provider\PasswordDigestValidator;
use Alcalyn\SilexWsse\Provider\WsseServiceProvider;

// Register Silex security
$app->register(new \Silex\Provider\SecurityServiceProvider(), array(
	'security.firewalls' => array(
		'api' => array(
			'pattern' => '^/api',
			'wsse' => true,
			'stateless' => true,
			'anonymous' => true,
			'users' => 'my_user_provider',
		),
	),
));

// SilexWsse needs a token validator service with a path where to store Wsse tokens
$app['security.wsse.token_validator'] = function () {
    $wsseCacheDir = 'my-project-path/var/cache/wsse-tokens';

    return new PasswordDigestValidator($wsseCacheDir);
};

// Register Wsse provider
$app->register(new WsseServiceProvider('api'));
```


## License

This project is under [MIT License](LICENSE).
