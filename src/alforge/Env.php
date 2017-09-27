<?php

namespace AlForge;

class Env extends Forge
{
    public function search($query)
    {
        echo $this->siteSearch($query);
    }

    public function execute($command)
    {
        $cmdParts = explode(' ', $command);

        $server = $this->getServerInfo($cmdParts[0]);
        $site = $this->getSiteInfo($cmdParts[0], $cmdParts[1]);

        $response = $this->apiRequest("https://forge.laravel.com/api/v1/servers/$cmdParts[0]/sites/$cmdParts[1]/env", "GET");

        $this->respond($response);
    }
}
