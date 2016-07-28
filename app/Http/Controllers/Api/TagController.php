<?php

namespace App\Http\Controllers\Api;

use App\Models\UserActionLog;
use Illuminate\Http\Request;
use \Response;
use \Auth;
use \App\Models\User;
use \App\Models\Tag;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Marcelgwerder\ApiHandler\Facades\ApiHandler;

class TagController extends ApiController
{
    public function index()
    {
        return ApiHandler::parseMultiple(Tag::query(), ['name'])->getResponse();
    }
    public function show($id)
    {
        return Tag::find($id)->toArray();
    }
    public function store(Request $request)
    {
        $data = $request->all();
        $obj = Tag::create($data);

        UserActionLog::saveAction($obj, "create");

        return $obj->toArray();
    }
    public function update(Request $request)
    {
        $data = $request->all();
        $obj = Tag::find($data['id']);
        $is_saved = $obj->update($data);

        if ($is_saved)
            UserActionLog::saveAction($obj, "update");

        return $obj->toArray();
    }
    public function destroy($id)
    {
        $obj = Tag::find($id);
        $is_destroyed = Tag::destroy($id);

        if ($is_destroyed)
            UserActionLog::saveAction($obj, "destroy");

        return $is_destroyed;
    }

    public function getAutoTags(Request $request)
    {
        $data = $request->all();
        return Tag::getAutoTags($data['tags_ids']);
    }
}
