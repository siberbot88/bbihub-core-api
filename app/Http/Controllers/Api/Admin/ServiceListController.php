<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Workshop;
use Dotenv\Validator;

class ServiceListController extends Controller
{
    public function index(){
        $list_services = Service::all();
        return response()->json(['message'=>'Success', 'data' => $list_services], 201);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [

        ]);
    }

    public function inform(Request $request, Workshop $workshop){

    }
}
