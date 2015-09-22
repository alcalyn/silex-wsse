<?php

namespace Alcalyn\SilexWSSE\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Alcalyn\WSSE\Security\Authentication\Provider\WsseTokenValidatorInterface;
use Alcalyn\WSSE\Security\Authentication\Provider\WsseProvider;
use Alcalyn\WSSE\Security\Http\EntryPoint\NoEntryPoint;
use Alcalyn\WSSE\Security\Http\Firewall\WsseListener;

class WsseServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string
     */
    private $firewallName;

    /**
     * @var WsseTokenValidatorInterface
     */
    private $digestValidator;

    /**
     * @param string $firewallName
     * @param WsseTokenValidatorInterface $digestValidator
     */
    public function __construct($firewallName, WsseTokenValidatorInterface $digestValidator)
    {
        $this->firewallName = $firewallName;
        $this->digestValidator = $digestValidator;
    }

    /**
     * {@InheritDoc}
     */
    public function register(Container $app)
    {
        $app['security.authentication_listener.factory.wsse'] = $app->protect(function ($name, $options) use ($app) {
            // define the authentication provider object
            $app['security.authentication_provider.'.$name.'.wsse'] = function () use ($app) {
                return new WsseProvider(
                    $app['security.user_provider.'.$this->firewallName],
                    $app['security.user_checker'],
                    $this->digestValidator
                );
            };

            // define the authentication listener object
            $app['security.authentication_listener.'.$name.'.wsse'] = function () use ($app) {
                // use 'security' instead of 'security.token_storage' on Symfony <2.6
                return new WsseListener($app['security.token_storage'], $app['security.authentication_manager']);
            };

            // define the entry point object
            $app['security.entry_point.'.$name.'.wsse'] = function () {
                return new NoEntryPoint();
            };

            return array(
                // the authentication provider id
                'security.authentication_provider.'.$name.'.wsse',
                // the authentication listener id
                'security.authentication_listener.'.$name.'.wsse',
                // the entry point id
                'security.entry_point.'.$name.'.wsse',
                // the position of the listener in the stack
                'pre_auth'
            );
        });
    }
}
