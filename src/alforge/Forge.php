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
        
        if( !file_exists($this->cacheDir) ){
            mkdir($this->cacheDir);
        }
        
        $this->token = file_exists($this->authCache) ? file_get_contents($this->authCache): '';
        $this->data = file_exists($this->dataCache) ? json_decode(file_get_contents($this->dataCache)) : $this->loadCache();
    }

    public function loadCache($reload = False)
    {
        $this->data = [];
        $this->data['servers'] = [];
        
        $response = $this->apiRequest('https://forge.laravel.com/api/v1/servers', 'GET');

        $serverCount = 0;
        
        if( $response->servers ){
            foreach( $response->servers as $server ){
                $serverCount++;
                $sitesResponse = $this->apiRequest('https://forge.laravel.com/api/v1/servers/'.$server->id.'/sites/', 'GET');
                $server->sites = $sitesResponse->sites;
                array_push($this->data['servers'], $server);
            }

            file_put_contents($this->dataCache, json_encode($this->data));
        }

        if( $reload ){
            $this->respond("Loaded $serverCount servers!");
        }
    }

    public function apiRequest($url, $method = 'POST', $data = '')
    {
        $authorization = "Authorization: Bearer " . $this->token;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result);
    }

    public function setKey($key)
    {

        file_put_contents($this->authCache, $key);

        $this->respond('API Key Has Been Set');
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
            if( strpos($server->name, $query) > -1){
                $workflow->result()
                    ->uid($server->id)
                    ->title('Server: ' . $server->name)
                    ->subtitle($server->region)
                    ->arg($server->id)
                    ->valid(true);
            }

            foreach ($server->sites as $site) {
                if( strpos($site->name, $query) > -1 || strpos($server->name, $query) > -1 ){
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
                if( strpos($site->name, $query) > -1 || strpos($server->name, $query) > -1 ){
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
            if( strpos($server->name, $query) > -1){
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

        if(!$response){
            $this->respond('Action Cancelled');
            return False;
        }

        return True;
    }

    public function getServerInfo($server_id)
    {
        foreach ($this->data->servers as $server) {
            if( $server->id == $server_id ){
                return $server;
            }
        }
    }

    public function getSiteInfo($server_id, $site_id)
    {
        foreach ($this->data->servers as $server) {
            if( $server->id == $server_id ){
                foreach ($server->sites as $site) {
                    if($site->id == $site_id){
                        return $site;
                    }
                }
            }
        }
    }

}