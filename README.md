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
// Register Silex security
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'api' => array(
            'pattern' => '^/api',
            'wsse' => true,
            'stateless' => true,
            'users' => $myUserProvider,
        ),
    ),
));

// SilexWsse needs a token validator service with a path where to store Wsse tokens
$app['security.wsse.token_validator'] = function () {
    $wsseCacheDir = 'var/cache/wsse-tokens';

    return new PasswordDigestValidator($wsseCacheDir);
};

// Register Wsse provider
$app->register(new WsseServiceProvider('api'));
```

Then you can retrieve your authenticated user in controller like that:

``` php
$app->get('api/auth', function () use ($app) {
    $authenticatedUser = $app['user'];

    return 'Hello '.$app->escape($authenticatedUser->getUsername());
});
```

### Full example

Using a plain password encoder, and an user `toto` with password `pass`:

``` php
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
use Alcalyn\Wsse\Security\Authentication\Provider\PasswordDigestValidator;
use Alcalyn\SilexWsse\Provider\WsseServiceProvider;

$app = new Silex\Application();

$myUserProvider = function () {
    return new InMemoryUserProvider(array(
        'toto' => ['password' => 'pass'],
    ));
};

$app['security.default_encoder'] = function () {
    return new PlaintextPasswordEncoder();
};

// Register Silex security
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'api' => array(
            'pattern' => '^/api',
            'wsse' => true,
            'stateless' => true,
            'users' => $myUserProvider,
        ),
    ),
));

// SilexWsse needs a token validator service with a path where to store Wsse tokens
$app['security.wsse.token_validator'] = function () {
    $wsseCacheDir = 'var/cache/wsse-tokens';

    return new PasswordDigestValidator($wsseCacheDir);
};

// Register Wsse provider
$app->register(new WsseServiceProvider('api'));

$app->get('api/auth', function () use ($app) {
    $authenticatedUser = $app['user'];

    return 'Hello '.$app->escape($authenticatedUser->getUsername());
});

$app->run();
```

Then making the following http request with the `X-WSSE` header
(generated [here](http://www.teria.com/~koseki/tools/wssegen/)):

```
GET http://localhost/my-app/index.php/api/auth
X-WSSE: UsernameToken Username="toto", PasswordDigest="ieIS4sijyAW2ZrnvhvDOqBH+aSQ=", Nonce="NDlhNWE2M2YxNWQ2ZDk1NA==", Created="2016-07-31T12:46:16Z"
```

Returns the response:

```
200 OK
Date:  Sun, 31 Jul 2016 12:46:25 GMT

Hello toto
```


### Debugging

While implementing Wsse authentication, you should experience some authentication fail
with your Wsse token (date expired, already used nonce...).

To display the fail reason, you can display symfony authentication exception like that:

``` php
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;

$app->on('security.authentication.failure', function(AuthenticationFailureEvent $event) {
    echo $event->getAuthenticationException()->getMessage();
});
```

See Symfony [documentation about authentication events](http://symfony.com/doc/current/components/security/authentication.html#authentication-events).


## License

This project is under [MIT License](LICENSE).
