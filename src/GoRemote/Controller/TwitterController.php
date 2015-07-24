<?php
namespace GoRemote\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TwitterController
{
    public function authAction(Request $request, Application $app)
    {
		if ($app['session']->get('twitter_oauth_token') === null) {
			// get the request token
			$reply = $app['twitter']->oauth_requestToken([
				'oauth_callback' => $request->getUri()
			]);

			if (empty($reply)) {
				return new Response('Reply is empty');
			}

			// store the token
			$app['twitter']->setToken($reply->oauth_token, $reply->oauth_token_secret);

			$app['session']->set('twitter_oauth_token', $reply->oauth_token);
			$app['session']->set('twitter_oauth_token_secret', $reply->oauth_token_secret);
			$app['session']->set('twitter_oauth_verify', true);

			// redirect to auth website
			return $app->redirect($app['twitter']->oauth_authorize());
		} elseif ($request->get('oauth_verifier') !== null && $app['session']->get('twitter_oauth_verify') !== null) {
			// verify the token
			$app['twitter']->setToken(
				$app['session']->get('twitter_oauth_token'), 
				$app['session']->get('twitter_oauth_token_secret')
				);

			$app['session']->set('twitter_oauth_verify', null);

			// get the access token
			$reply = $app['twitter']->oauth_accessToken([
				'oauth_verifier' => $request->get('oauth_verifier')
			]);

			// store the token (which is different from the request token!)
			$app['session']->set('twitter_oauth_token', $reply->oauth_token);
			$app['session']->set('twitter_oauth_token_secret', $reply->oauth_token_secret);

			// send to same URL, without oauth GET parameters
			return $app->json([
				'oauth_token' => $reply->oauth_token,
				'oauth_token_secret' => $reply->oauth_token_secret,
				]);
		}
        return new Response('oops');
    }
}
