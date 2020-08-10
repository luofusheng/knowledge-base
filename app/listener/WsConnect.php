<?php
declare (strict_types = 1);

namespace app\listener;

use think\swoole\Websocket;

class WsConnect
{
    /**
     * 事件监听处理
     *
     * @return mixed
     */
    public function handle($event, Websocket $ws)
    {
        $fd = $ws->getSender();
        session('fd', $fd);
    }    
}
