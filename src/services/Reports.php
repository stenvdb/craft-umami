<?php
/**
 * Umami plugin for Craft CMS 3.x
 *
 * Statistics and chart widgets for Umami
 *
 * @link      https://stenvdb.be
 * @copyright Copyright (c) 2020 Sten Van den Bergh
 */

namespace stenvdb\umami\services;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ServerException;
use stenvdb\umami\Umami;

use Craft;
use craft\base\Component;

/**
 * Reports Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Sten Van den Bergh
 * @package   Umami
 * @since     1.0.0
 */
class Reports extends Component
{
    // Public Methods
    // =========================================================================

    public function getRealtimeVisitors()
    {
        $response = $this->hitUmami('active', null, null);

        return $this->parseResponse($response);
    }

    public function getReport($before, $after, $unit)
    {
        $response = $this->hitUmami('pageviews', $before, $after, $unit);

        return $this->parseResponse($response);
    }

    public function getSiteStats($before, $after) {
        $response = $this->hitUmami('stats', $before, $after);

        return $this->parseResponse($response);
    }

    public function getTopPages($before, $after)
    {
        $response = $this->hitUmami('metrics', $before, $after);

        return $this->parseResponse($response);
    }

    // Private Methods
    // =========================================================================

    private function authenticate()
    {
        $client = new Client([
            'base_uri' => 'https://' . Umami::$plugin->getSettings()->getBaseUri() . '/'
        ]);

        $jar = new CookieJar;
        $response = $client->post('api/auth/login', [
            'json' => [
                'username' => Umami::$plugin->getSettings()->getUsername(),
                'password' => Umami::$plugin->getSettings()->getPassword(),
            ],
            'cookies' => $jar,
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8'
            ]
        ]);

        if (!$response->getStatusCode() === 200)
        {
            return;
        }

        // Save our auth cookie for reuse
        Craft::$app->getSession()->set('fa-auth', $jar->getCookieByName('umami.auth')->getValue());
    }

    private function getCookieJar()
    {
        if (is_null(Craft::$app->getSession()->get('fa-auth'))) {
            $this->authenticate();
        }

        $jar = CookieJar::fromArray([
            'umami.auth' => Craft::$app->getSession()->get('fa-auth')
        ], parse_url('https://' . Umami::$plugin->getSettings()->getBaseUri() . '/')['host']);

        return $jar;
    }

    private function hitUmami($endpoint, $before, $after, $unit = 'day')
    {
        $jar = $this->getCookieJar();

        $baseUri = 'https://' . Umami::$plugin->getSettings()->getBaseUri() . '/';

        if (substr($baseUri, -1) !== '/')
        {
            $baseUri .= '/';
        }

        $siteId = $this->getSiteId();

        $baseUri .= 'api/website/'.$siteId.'/';

        $client = new Client([
            'base_uri' => $baseUri
        ]);

        $query = [];

        if (isset($before) && isset($after))
        {
            $query['end_at'] = $before . '999';
            $query['start_at'] = $after . '000';
            $query['unit'] = $unit;
            $query['tz'] = 'Europe/Brussels';
        }

        if ($endpoint === 'metrics') {
            $query['type'] = 'url';
        }

        return $client->get($endpoint, [
            'cookies' => $jar,
            'query' => $query
        ]);
    }

    private function getSiteId()
    {
        $jar = $this->getCookieJar();

        $baseUri = 'https://' . Umami::$plugin->getSettings()->getBaseUri() . '/';

        $endpoint = $baseUri . 'api/websites';

        $client = new Client([
            'base_uri' => $baseUri
        ]);

        $response = $client->get($endpoint, [
            'cookies' => $jar
        ]);

        if (!$response->getStatusCode() === 200)
        {
            return;
        }

        $data = json_decode($response->getBody()->getContents());

        $siteId = null;
        foreach($data as $site) {
            if (Umami::$plugin->getSettings()->getWebsiteId() == $site->website_uuid) {
                $siteId = $site->website_id;
                break;
            }
        }

        return $siteId;
    }

    private function parseResponse($response)
    {
        if (!$response->getStatusCode() === 200)
        {
            return;
        }

        $jsonData = $response->getBody()->getContents();

        return $jsonData;
    }
}
