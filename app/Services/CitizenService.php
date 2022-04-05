<?php

namespace App\Services;

use App\Models\Citizen;
use App\Models\Region;
use App\Models\Role;
use App\Repositories\CitizenRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CitizenService
{

    /**
     * @var CitizenRepository
     */
    private $repository;

    public function __construct()
    {
        $this->repository = new CitizenRepository();
    }

    public function guard()
    {
        return Auth::guard();
    }

//    public function getAll(Request $request)
//    {
//        $user = $this->guard()->user();
//        $query = Citizen::query();
//
//        if ($user->role_id == Citizen::REGION){
//            $query->where(['region_id' => $user->region_id]);
//        }
//        if ($user->role_id == Citizen::DISTRICT){
//            $query->where(['district_id' => $user->district_id]);
////                ->with('region:id,name_cyrl')
////                ->with('district')
////                ->get();
//        }
////
//        return [
//            'current_page' => $request->page ?? 1,
//            'per_page' => $request->limit,
//            'data' => $query
//                ->with('region:id,name_cyrl')
//                ->with('district')
//                ->get(),
//            'total' => $query->count() < $request->limit ? $citizens->count() : -1
//        ];
//    }

    public function getAll(Request $request)
    {
        $user = Auth::user();
        $query = Citizen::query()
            ->with('region:id,name_cyrl')
            ->with('district')
            ->with('socialStatus');


        if ($user->role_id == Citizen::REGION){
            $query->where(['region_id' => $user->region_id]);
        }
        if ($user->role_id == Citizen::DISTRICT){
            $query->where(['district_id' => $user->district_id]);
        }

        if (!empty($request->all()['region_id'])){
            $query->where(['region_id' => $request->all()['region_id']]);
        }
        if (!empty($request->all()['district_id'])){
            $query->where(['district_id' => $request->all()['district_id']]);
        }
        if (!empty($request->all()['social_id'])){
            $query->where(['social_id' => $request->all()['social_id']]);
        }
        if (!empty($request->all()['l_name'])){
            $query->where('citizens.l_name', 'like', '%'. $request->all()['l_name'].'%');
        }
        if (!empty($request->all()['f_name'])){
            $query->where('citizens.f_name', 'like', '%'. $request->all()['f_name'].'%');
        }
        if (!empty($request->all()['m_name'])){
            $query->where('citizens.m_name', 'like', '%'. $request->all()['m_name'].'%');
        }
        if (!empty($request->all()['passport'])){
            $query->where('citizens.passport', 'like', '%'. $request->all()['passport'].'%');
        }

        $query->paginate($request->limit)->toArray();
//        if($request->has('getAll')){
//            $query = $query->paginate($query->count());
//        } else {
//            $query = $query->paginate($request->get('limit', 30));
//        }

        return [
            'current_page' => $request->page ?? 1,
            'per_page' => $request->limit,
            'data' =>$query->get(),
            'total' => $query->count() < $request->limit ? $query->count() : -1,
        ];



    }
//    public function store($request)
//    {
//        $user = Auth::user();
//        $validator = $this->repository->toValidate($request->all());
//        $msg = "";
//        $citizen = $this->repository->store($request);
//        return response()->successJson(['citizen' => $citizen]);
//
////        if (!$validator->fails()){
////            if ($user->role_id == Citizen::ADMIN){
////                return response()->errorJson('Рухсат мавжуд емас', 101);
////            }
////            if ($user->role_id == Citizen::REGION){
////                return response()->errorJson('Рухсат мавжуд емас', 101);
////            }
////            if ($user->role_id == Citizen::DISTRICT){
////                if ($request->city_id != $user->city_id){
////                    return response()->errorJson('Рухсат мавжуд емас', 101);
////                }
////                $citizen = $this->repository->store($request);
////                return response()->successJson(['citizen' => $citizen]);
////            }
////        }
////        else{
////            $errors = $validator->failed();
////            if(empty($errors)) {
////                $msg = "Соҳалар нотўғри киритилди";
////            }
////            return response()->errorJson($msg, 400, $errors);
////        }
//
//    }

    public function store($request)
    {

        $user = Auth::user();

        $validator = $this->repository->toValidate($request->all());
        $msg = "";

//        $citizen = $this->repository->store($request);

//        return response()->successJson(['citizen' => $citizen]);

        if (!$validator->fails()){


            if ($user->role_id == Role::ADMIN){
                return response()->errorJson('Рухсат мавжуд емас', 101);
            }
            if ($user->role_id == Role::REGION){
                return response()->errorJson('Рухсат мавжуд емас', 101);
            }
            if ($user->role_id == Role::DISTRICT){
                $citizen = $this->repository->store($request);
                return response()->successJson(['citizen' => $citizen]);
            }
        }
        else{
            $errors = $validator->failed();
            if(empty($errors)) {
                $msg = "Соҳалар нотўғри киритилди";
            }
            return response()->errorJson($msg, 400, $errors);
        }

    }

    public function show($id)
    {
        $user = Auth::user();
//        $query = Citizen::query();
        $query = Citizen::query()
            ->with('region:id,name_cyrl')
            ->with('district')
            ->with('socialStatus');
        $query->where(['id' => $id]);
//            ->with('region:id, name_cyrl')
//            ->with('district');

        if (empty($query->first())){
            return response()->errorJson('Бундай ид ли фойдаланувчи мавжуд емас', 409);
        }
        return $query->first();
    }

    public function update($request, $id){
//        $msg = "";
//        $validator = $this->repository->toValidate($request->all());
//
//        $citizen = $this->repository->update($request, $id);
//        return ['status' => 200, 'citizen' => $citizen];
//        $citizen = DB::table('citizens')->where(['id' => $id])->first();

        $msg = "";
        $validator = $this->repository->toValidate($request->all());

        if (!$validator->fails()) {

            if(!$this->repository->checkCitizen($request->passport, $id)) {
                $citizen = $this->repository->update($request, $id);
                return  ['status' => 200, 'citizen' => $citizen];
            } else {
                return ['msg' => 'Bu ma\'lumotlar bazada mavjud', 'status' => 409];
            }
        } else {
            $errors = $validator->failed();
            if(empty($errors)) {
                $msg = "Соҳалар нотўғри киритилди";
            }
            return ['msg' => $msg, 'status' => 422, 'error' => $errors];
        }
    }

}