<?php

namespace app\admin\controller;

use app\admin\model\SystemMessage as ModelSystemMessage;
use support\Request;

class SystemMessage
{
    public function getSystemMessageList(Request $request)
    {
        $data = $request->only(['smid', 'title', 'order', 'sort', 'page', 'limit']);
        $result = ModelSystemMessage::getSystemMessageList($data);
        return json($result);
    }
    public function getSystemMessage(Request $request)
    {
        $data = $request->only(['smid']);
        $result = ModelSystemMessage::getSystemMessage($data);
        return json($result);
    }
    public function createSystemMessage(Request $request)
    {
        $data = $request->only(['title', 'content']);
        $result = ModelSystemMessage::createSystemMessage($data);
        return json($result);
    }
    public function modifySystemMessage(Request $request)
    {
        $data = $request->only(['smid', 'title', 'content']);
        $result = ModelSystemMessage::modifySystemMessage($data);
        return json($result);
    }
    public function deleteSystemMessage(Request $request)
    {
        $data = $request->only(['smid']);
        $result = ModelSystemMessage::deleteSystemMessage($data);
        return json($result);
    }
}
