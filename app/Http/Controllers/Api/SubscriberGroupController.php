<?php

namespace App\Http\Controllers\Api;

use App\UserActionLog;
use Illuminate\Http\Request;
use \Response;
use \Auth;
use \App\SubscriberGroup;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class SubscriberGroupController extends Controller
{
    public function index()
    {
        return Response::json(
            SubscriberGroup::all()->toArray(),
            200
        );
    }
    public function show($id)
    {
        return Response::json(
            SubscriberGroup::find($id)->toArray(),
            200
        );
    }
    public function store(Request $request)
    {
        $data = $request->all();
        $obj = SubscriberGroup::create($data);

        if(isset($data['subscribers_ids'])){
            $obj->subscribers_ids = $data['subscribers_ids'];
            $obj->save();
        }

        UserActionLog::saveAction($obj,"create");
        return Response::json(
            $obj->toArray(),
            200
        );
    }
    public function update(Request $request)
    {
        $data = $request->all();
        $is_saved = SubscriberGroup::find($data['id'])->update($data);
        $obj =  SubscriberGroup::find($data['id']);

        if(isset($data['subscribers_ids'])){
            $obj->subscribers_ids = $data['subscribers_ids'];
            $obj->save();
        }

        if ($is_saved)
            UserActionLog::saveAction($obj,"update");
        return Response::json(
            $obj,
            $is_saved ? 200 : 400
        );
    }
    public function destroy($id)
    {
        $obj = SubscriberGroup::find($id);
        $is_destroyed = SubscriberGroup::destroy($id);
        if ($is_destroyed)
            UserActionLog::saveAction($obj,"destroy");
        return Response::json(
            $is_destroyed ? 200 : 400
        );
    }
}
