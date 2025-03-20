<?php

namespace app\admin\controller;

use app\admin\model\Page as ModelPage;
use support\Request;

class Page
{
    public function getPageList(Request $request)
    {
        $data = $request->only(['pid', 'title', 'order', 'sort', 'page', 'limit']);
        $result = ModelPage::getPageList($data);
        return json($result);
    }
    public function getPage(Request $request)
    {
        $data = $request->only(['pid']);
        $result = ModelPage::getPage($data);
        return json($result);
    }
    public function createPage(Request $request)
    {
        $data = $request->only(['title', 'content']);
        $result = ModelPage::createPage($data);
        return json($result);
    }
    public function modifyPage(Request $request)
    {
        $data = $request->only(['pid', 'title', 'content']);
        $result = ModelPage::modifyPage($data);
        return json($result);
    }
    public function deletePage(Request $request)
    {
        $data = $request->only(['pid']);
        $result = ModelPage::deletePage($data);
        return json($result);
    }
}
