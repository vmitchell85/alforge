<?php

namespace AlForge;

class Reboot extends Forge
{
    public function search($query)
    {
        echo $this->serverSearch($query);
    }

    public function execute($server_id)
    {
        $server = $this->getServerInfo($server_id);

        if ($this->confirm("Are you sure you want to reboot the server `$server->name`?")) {
            $response = $this->apiRequest("https://forge.laravel.com/api/v1/servers/$server->id/reboot");
            
            $data = json_decode($response);
            
            $this->respond(
                "Command sent to Forge",
                ["push_title" => "Rebooting `$server->name`"]
            );
        }
    }
}
