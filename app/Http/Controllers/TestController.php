<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function test(Request $request)
    {
        //if($request->password!='a789456b') return false;
        if($action = $request->action) return $this->$action();
        return false;
    }

    private function insertSystemRolePermission()
    {
        $ids = \App\Http\Model\SystemPermission::get()->pluck('id')->all();
        $path=[];
        foreach ($ids as $k=>$value){
            $path[$k]['role_id'] = 1;
            $path[$k]['agent_id'] = 1;
            $path[$k]['permission_id'] = $value;
        }
        \App\Http\Model\SystemRolePermission::insert($path);
    }

    private function insertSystemPermission()
    {
        $routes = \Route::getRoutes();
        $path=[];
        foreach ($routes as $k=>$value){
            $path[$k]['api_route'] = $value->uri;
            $path[$k]['name'] = $value->action['as'];
            $path[$k]['method'] = $value->methods[0];
        }
        \App\Http\Model\SystemPermission::insert($path);
    }

    private function refreshToken()
    {
        $http = new \GuzzleHttp\Client;
        $response = $http->post('http://localhost:7777/oauth/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => 'def502007e00a2f5e2422e92021185a4b6899b9c72547bd87f2e03e4797d1b6a07976a92e1bef4f18fe3f551482d0f6f14e57f1f7156556cee965fe174b6f4e6f04e565c62986b826846d7df5fa8efe02d4f846fc1167351107ebaf4f0c3c5032586cbd5f9cf48a81ffa379140397913f77dac55651caf5c054eb4afb9a6e990c6e26fab40079907eedf865d9752a030b860f1dac44103d3ffcd11d9dba8a6f8a799fe8c6dca7fe90b16e8ed9b5360de7f440e9169cd0254f6fa78251429603d8b3e5e4281ceabe904c020a2e04306fa3bf292f61cfdced590f48a97bd4c90c42d33544f4c773bd2f9a779e1ab13d1ed1f523ebb2b4634d7c0b1a974efc64cc688a61e2ad2f83a1367e86700354cc4aed040cf2b14cc8c7979c6b5a167002859bbf2895b09a011169a0e9fb86ef9fb9c32f5b6b24d96da802d2334867ec405a323d2cc04375bf96eef4cffa167cea4799daff5936fe82688fc36979f4682b611f8',
                'client_id' => 6,
                'client_secret' => 'ONjIiDAupLsvWRgLc69guRIPv49RdWbFN44CKRiE'
            ],
        ]);
        return json_decode((string)$response->getBody(), true);
    }
}
