<?php

namespace App\Http\Controllers\Api;

use App\Models\UserActionLog;
use Illuminate\Http\Request;
use \Response;
use \Auth;
use \App\Models\User;
use \App\Models\Dictionary;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Marcelgwerder\ApiHandler\Facades\ApiHandler;

class DictionaryController extends ApiController
{
    public function index()
    {
        return ApiHandler::parseMultiple(Dictionary::query(), ['key', 'name', 'description'])->getResponse();
    }
    public function show($id)
    {
        return Dictionary::find($id)->toArray();
    }
    public function store(Request $request)
    {
        $data = $request->all();
        $obj = Dictionary::create($data);

        UserActionLog::saveAction($obj, "create");

        return $obj->toArray();
    }
    public function update(Request $request)
    {
        $data = $request->all();
        $obj = Dictionary::find($data['id']);
        $is_saved = $obj->update($data);

        if ($is_saved)
            UserActionLog::saveAction($obj, "update");

        return $obj->toArray();
    }
    public function destroy($id)
    {
        $obj = Dictionary::find($id);        
        $is_destroyed = Dictionary::destroy($id);

        if ($is_destroyed)
            UserActionLog::saveAction($obj, "destroy");

        return $is_destroyed;
    }
}
