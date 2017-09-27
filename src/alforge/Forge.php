<?php

namespace AlForge;

use Alfred\Workflows\Workflow;

class Forge
{
    protected $data;
    protected $token;
    
    protected $cacheDir;
    protected $dataCache;
    protected $authCache;

    public function __construct()
    {
        $this->cacheDir = getenv('alfred_workflow_cache');
        $this->dataCache = getenv('alfred_workflow_cache') . '/data.json';
        $this->authCache = getenv('alfred_workflow_cache') . '/auth.txt';
        
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir);
        }

        if (!file_exists($this->dataCache)) {
            file_put_contents($this->dataCache, json_encode(["servers" => []]));
        }

        if (!file_exists($this->authCache)) {
            file_put_contents($this->authCache, '');
        }
        
        $this->token = file_get_contents($this->authCache);
        $this->data = json_decode(file_get_contents($this->dataCache));
    }

    public function loadCache()
    {
        $this->data = [];
        $this->data['servers'] = [];
        
        $response = $this->apiRequest('https://forge.laravel.com/api/v1/servers', 'GET');

        $data = json_decode($response);

        $serverCount = 0;
        
        if ($data->servers) {
            foreach ($data->servers as $server) {
                $serverCount++;
                $sitesResponse = $this->apiRequest('https://forge.laravel.com/api/v1/servers/'.$server->id.'/sites/', 'GET');
                $sitesData = json_decode($sitesResponse);
                $server->sites = $sitesData->sites;
                array_push($this->data['servers'], $server);
            }

            file_put_contents($this->dataCache, json_encode($this->data));
        }

        $this->respond("Loaded $serverCount servers into cache!");
    }

    public function apiRequest($url, $method = 'POST', $data = '')
    {
        $authorization = "Authorization: Bearer " . $this->token;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json' , $authorization ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($this->requestHasError($result, $httpcode)) {
            die();
        }

        return $result;
    }

    public function requestHasError($result, $code)
    {
        if ($code >= 400 || strpos($result, '<html') > -1) {
            $errorMap = [
                400 => "Valid data was given but the request has failed.",
                401 => "No valid API Key was given.",
                404 => "The request resource could not be found.",
                422 => "The payload has missing required parameters or invalid data was given.",
                429 => "Too many attempts.",
                500 => "Request failed due to an internal error in Forge.",
                503 => "Forge is offline for maintenance."
            ];

            if ($errorMap[$code]) {
                $this->emitError($errorMap[$code]);
            } else {
                $this->emitError("An unknown error occured, maybe try re-setting your API Key in Alfred");
            }

            return true;
        }

        return false;
    }

    public function setKey($key)
    {
        $this->token = $key;
        file_put_contents($this->authCache, $key);
        $this->loadCache();
    }

    public function clearData()
    {
        unlink($this->authCache);
        unlink($this->dataCache);

        $this->respond('All Data Deleted!');
    }

    public function respond($arg = '', $variables = [])
    {
        $defaultVars = [
            "push_title" => "AlForge Notification"
        ];

        $alfredObj = [
            "alfredworkflow" => [
                "arg" => $arg,
                "variables" => array_merge($defaultVars, $variables)
            ]
        ];

        echo json_encode($alfredObj);
    }

    public function allSearch($query)
    {
        $workflow = new Workflow;

        foreach ($this->data->servers as $server) {
            if (strpos($server->name, $query) > -1) {
                $workflow->result()
                    ->uid($server->id)
                    ->title('Server: ' . $server->name)
                    ->subtitle($server->region)
                    ->arg($server->id)
                    ->valid(true);
            }

            foreach ($server->sites as $site) {
                if (strpos($site->name, $query) > -1 || strpos($server->name, $query) > -1) {
                    $workflow->result()
                        ->uid($site->id)
                        ->title('Site: ' . $site->name)
                        ->subtitle($server->name)
                        ->arg($server->id . ' ' . $site->id)
                        ->mod('cmd', 'Deploy ' . $site->name, 'deploy ' . $server->id . ' ' . $site->id)
                        ->valid(true);
                }
            }
        }


        return $workflow->output();
    }

    public function siteSearch($query)
    {
        $workflow = new Workflow;

        foreach ($this->data->servers as $server) {
            foreach ($server->sites as $site) {
                if (strpos($site->name, $query) > -1 || strpos($server->name, $query) > -1) {
                    $workflow->result()
                        ->uid($site->id)
                        ->title('Site: ' . $site->name)
                        ->subtitle($server->name)
                        ->arg($server->id . ' ' . $site->id)
                        ->valid(true);
                }
            }
        }

        return $workflow->output();
    }

    public function serverSearch($query)
    {
        $workflow = new Workflow;

        foreach ($this->data->servers as $server) {
            if (strpos($server->name, $query) > -1) {
                $workflow->result()
                    ->uid($server->id)
                    ->title('Server: ' . $server->name)
                    ->subtitle($server->region)
                    ->arg($server->id)
                    ->valid(true);
            }
        }

        return $workflow->output();
    }

    public function confirm($text = "Are you sure?")
    {
        $response = exec("echo $(osascript -e 'display dialog \"$text\" buttons {\"Cancel\",\"Confirm\"} default button 2 with title \"Confirm alForge Command\" with icon file \"System:Library:CoreServices:CoreTypes.bundle:Contents:Resources:AlertStopIcon.icns\"')");

        if (!$response) {
            $this->respond('Action Cancelled');
            return false;
        }

        return true;
    }

    public function emitError($text)
    {
        $response = exec("echo $(osascript -e 'tell application \"Alfred 3\" to run trigger \"push\" in workflow \"com.vmitchell85.alForge\" with argument \"$text\"')");
    }

    public function getServerInfo($server_id)
    {
        foreach ($this->data->servers as $server) {
            if ($server->id == $server_id) {
                return $server;
            }
        }
    }

    public function getSiteInfo($server_id, $site_id)
    {
        foreach ($this->data->servers as $server) {
            if ($server->id == $server_id) {
                foreach ($server->sites as $site) {
                    if ($site->id == $site_id) {
                        return $site;
                    }
                }
            }
        }
    }
}
