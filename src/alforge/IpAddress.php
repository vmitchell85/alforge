<?php

namespace AlForge;

class IpAddress extends Forge
{
    public function search($query)
    {
        echo $this->serverSearch($query);
    }

    public function execute($server_id)
    {
        $server = $this->getServerInfo($server_id);

        $this->respond($server->ip_address);
    }
}
