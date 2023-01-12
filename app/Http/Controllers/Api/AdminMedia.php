<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdminMedia as Media;
use App\Http\Controllers\Api\Tools\Util;
use Validator;
class AdminMedia extends Controller
{

    public function addMedia(Request $req, $mediaID = false)
    {
        $this->user = Util::getUserDetail();

        if($req->has('series_id')){
            $mainType ="series";
        }
        else{
        preg_match_all("/movies|trailers|series/i", $req->type, $dummy);
        $mainType =$dummy[0];
        $mainType = (!isset($mainType[0]))?'Videos':$mainType[0];
        }
        $client =$this->user;
        /*
        $client = ($this->user->role == 'admin' && $clientId) ?
            Client::find($clientId) :
             $user = ($this->user->role == 'admin')? $this->user:Client::find($this->user->id);

            if ($this->user->role != 'admin' && $client->media()->count()>3) {
                return response()->json(['status' => false, 'message' => 'Only 4 Media Files Can Be Added!']);
            }
            */
        $maxSize =($req->type == 'trailers')?"|max:21024":"";
        $maxDur =($req->type == 'trailers')?"|max:30":"";
        $rule = [
            'title' => 'required|string',
            'des' => 'nullable|string',
            'media' => 'required|mimes:jpeg,jpg,gif,png',
            'thumbs' => 'nullable|mimes:jpeg,jpg,gif,png',
             'type'=>'required|string',
             'category'=>'required|string',
        ];
        $data = [
             'title' => $req->title,
             'des' => $req->des,
             'subDes'=>$req->subDes,
             'type'=>$mainType,
             ];


           $seriesInfo =[];

             if(stripos($req->type,"series")!==false || $req->has('series_id')){
                $rule=array_merge(\Arr::except($rule,['type','category']),['season'=>'required|integer',
                    'episode'=>'required|integer',
                    'season'=>'required|integer|exists:series_seasons,id,series_id,'.$req->series_id
                    ,'series_id'=>["required","integer","exists:series,id",

                    \Illuminate\Validation\Rule::unique('series_media','series_id')
                   ->where('season',$req->season)->where('episode',$req->episode)
                   ]
                     ]);

                 $filter['season']=$req->season;
                 $filter['episode']=$req->episode;
                }
                else{
                    try{

                     $typeID=\App\Models\MediaType::firstOrCreate(['name'=>$req->type])->id;
                     $filter =['media_type_id'=>$typeID
                    ,'admin_media_category_id'=>\App\Models\AdminMediaCategory::firstOrCreate([
                    'category'=>$req->category,
                    'media_type_id'=>$typeID
                    ])->id];
                    }
                     catch (\illuminate\Database\QueryException $e) {
                     return response()->json(['status'=>false,'message'=>'Category Or Type are Missing']);

                    }
                }

        if (
            $req->hasFile('media') &&
            in_array($req->file('media')->extension(), ['mp4', '3gp', 'mov', 'avi', 'webm'])
        ) {
            $rule['media'] = $rule['media'] . ",webm,mp4,3gp,mov,avi $maxSize";
            $rule['duration'] = "required|integer $maxDur";

            $data['duration'] = $req->duration;
        } else {
            $rule['media'] = $rule['media'].$maxSize;
        }

        $checkValid = Validator::make(array_merge($req->all(),['mediaID'=>$mediaID] ), $rule, [
            'series_id.unique' => 'This Episode Allready Exists']);

        if ($checkValid->fails()) {

            return response()->json(['status' => false, 'message' => 'Invalid Data', 'errors' => $checkValid->errors()],422);
        }
        else if(!$checkValid->fails() && $req->has('series_id')) {

            $series = \App\Models\Series::find($req->series_id);

            $typeID=$series->media_type_id;

           $filter =['media_type_id'=>$typeID
           ,'admin_media_category_id'=>$series->admin_media_category_id];
          }

        if ($req->hasFile('media')) {

            $file = $req->file('media');
            $type = $file->extension();
            $fid="f".$this->user->id;
            $data['url'] = bin2hex($fid)."_".uniqid() . '_media' . time() . '.' . $type;
            $file->storeAs("public/media/",$data['url']);
            $data['url']= request()->getSchemeAndHttpHost().'/storage/media/'.$data['url'];
        }

        if ($req->hasFile('thumbs')) {

            $file = $req->file('thumbs');
            $type = $file->extension();
            $fid="f".$this->user->id;
            $data['thumbs'] = bin2hex($fid)."_".uniqid() . '_media' . time() . '.' . $type;
            $file->storeAs("public/media/",$data['thumbs']);
            $data['thumbs']= request()->getSchemeAndHttpHost().'/storage/media/'.$data['thumbs'];
        }

        try {

            $media = $client->media()->create($data);

            $media->filterMedia()->attach([$media->id=> $filter]);
            if($req->has('series_id')){

                $seriesInfo =array_merge($filter,['series_id'=>$req->series_id ,
                'season'=>$req->season,'episode'=>$req->episode]);
                $media->filterSeries()->attach([$media->id=>$seriesInfo]);
            }
            return response()->json(['status' =>true, 'message' => 'Media Added!']);
        } catch (\illuminate\Database\QueryException $e) {

            $errorCode = $e->errorInfo[1];

            if ($errorCode == 1062)
                return response()->json(['status' =>false, 'message' =>'This Episode Allready Exists']);

                return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
            }
    }


   public function updateMedia(Request $req, $mediaID = false)
    {
        $this->user = Util::getUserDetail();

        if($req->has('series_id')){
            $mainType ="series";
        }
        else{
        preg_match_all("/movies|trailers|series/i", $req->type, $dummy);
        $mainType =$dummy[0];
        $mainType = (!isset($mainType[0]))?'Videos':$mainType[0];
        }
        $client =$this->user;

        $maxSize =($req->type == 'trailers')?"|max:21024":"";
        $maxDur =($req->type == 'trailers')?"|max:30":"";
        $rule = [
            'mediaId'=>"required|integer|exists:admin_media,id",
            'title' => 'nullable|string',
            'des' => 'nullable|string',
            'media' => 'nullable|mimes:jpeg,jpg,gif,png',
            'thumbs' => 'nullable|mimes:jpeg,jpg,gif,png',
             //'type'=>'required|string',
             //'category'=>'required|string',
        ];
        $data = [
             'title' => $req->title,
             'des' => $req->des,
             //'subDes'=>$req->subDes,
             //'type'=>$mainType,
             ];


           $seriesInfo =[];

             if(stripos($req->type,"series")!==false || $req->has('series_id')){
                $rule=array_merge(\Arr::except($rule,['type','category']),['season'=>'nullable|integer',
                    'episode'=>'nullable|integer',
                    'season'=>'nullable|integer|exists:series_seasons,id,series_id,'.$req->series_id
                    ,'series_id'=>["nullable","integer","exists:series,id",

                    \Illuminate\Validation\Rule::unique('series_media','series_id')
                   ->where('season',$req->season)->where('episode',$req->episode)
                   ]
                     ]);

                 $filter['season']=$req->season;
                 $filter['episode']=$req->episode;
                }
                else{
                    try{

                     $typeID=\App\Models\MediaType::firstOrCreate(['name'=>$req->type])->id;
                     $filter =['media_type_id'=>$typeID
                    ,'admin_media_category_id'=>\App\Models\AdminMediaCategory::firstOrCreate([
                    'category'=>$req->category,
                    'media_type_id'=>$typeID
                    ])->id];
                    }
                     catch (\illuminate\Database\QueryException $e) {
                     return response()->json(['status'=>false,'message'=>'Category Or Type are Missing']);

                    }
                }

        if (
            $req->hasFile('media') &&
            in_array($req->file('media')->extension(), ['mp4', '3gp', 'mov', 'avi', 'webm'])
        ) {
            $rule['media'] = $rule['media'] . ",webm,mp4,3gp,mov,avi $maxSize";
            $rule['duration'] = "required|integer $maxDur";

            $data['duration'] = $req->duration;
          } else {
            $rule['media'] = $rule['media'].$maxSize;
          }

        $checkValid = Validator::make(array_merge($req->all(),['mediaId'=>$mediaID] ), $rule, [
            'series_id.unique' => 'This Episode Allready Exists']);

        if ($checkValid->fails()) {

            return response()->json(['status' => false, 'message' => 'Invalid Data', 'errors' => $checkValid->errors()],422);
        }
        else if(!$checkValid->fails() && $req->has('series_id')) {

            $series = \App\Models\Series::find($req->series_id);

            $typeID=$series->media_type_id;

           $filter =['media_type_id'=>$typeID
           ,'admin_media_category_id'=>$series->admin_media_category_id];
          }

        if ($req->hasFile('media')) {

            $file = $req->file('media');
            $type = $file->extension();
            $fid="f".$this->user->id;
            $data['url'] = bin2hex($fid)."_".uniqid() . '_media' . time() . '.' . $type;
            $file->storeAs("public/media/",$data['url']);
            $data['url']= request()->getSchemeAndHttpHost().'/storage/media/'.$data['url'];
        }

        if ($req->hasFile('thumbs')) {

            $file = $req->file('thumbs');
            $type = $file->extension();
            $fid="f".$this->user->id;
            $data['thumbs'] = bin2hex($fid)."_".uniqid() . '_media' . time() . '.' . $type;
            $file->storeAs("public/media/",$data['thumbs']);
            $data['thumbs']= request()->getSchemeAndHttpHost().'/storage/media/'.$data['thumbs'];

    }

        try {

            $media = Media::find($mediaID);
            $media->update(array_filter($data));

            $media->filterMedia()->update($filter);

            if($req->has('series_id')){

                $seriesInfo =array_merge($filter,['series_id'=>$req->series_id ,
                'season'=>$req->season,'episode'=>$req->episode]);
                $media->filterSeries()->update(array_filter($seriesInfo));
            }
            return response()->json(['status' =>true, 'message' => 'Media Updated!']);
        } catch (\illuminate\Database\QueryException $e) {

            $errorCode = $e->errorInfo[1];

            if ($errorCode == 1062)
                return response()->json(['status' =>false, 'message' =>'This Episode Allready Exists']);

                return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
            }
    }




    public function removeMedia($id){

        $idValidate= Validator::make(['mediaId'=>$id],['mediaId'=>'required|integer|exists:admin_media,id,user_id,1']);

        if($idValidate->fails()){

            return response()->json(['status'=>false,'message'=>'Media ID is not Valid'],422);
        }

        try{

           Media::find($id)->delete();

            return response()->json(['status'=>true,'message'=>'Media Record Removed!']);


        }
        catch(\Throwable $th){


            return response()->json(['status'=>false,'message'=>'Fails to Remove Media'],500);
        }

    }






    public function searchAllMedia($search){


         $clientMedia = Media::whereHas('filterMedia')->select('admin_media.*','media_types.name as category','admin_media_categories.category as subCategory'
         ,'media_filter.season','media_filter.episode')
         //->where('title','LIKE',"%%$search%%")
         ->leftjoin('media_filter','admin_media.id','media_filter.admin_media_id')
         ->leftjoin('media_types','media_filter.media_type_id','media_types.id')
         ->leftjoin('admin_media_categories','media_filter.admin_media_category_id','admin_media_categories.id')
         ->selectRaw('DATE_FORMAT(admin_media.updated_at, "%d %b %y") as date')
         ->search($search)
         ->withCount('comments','likes')
         ->paginate(15);


      return response()->json(['status'=>true,'message'=>$clientMedia->count().' media found','media'=>$clientMedia]);

       }



    public function fetchAllMedia($type,$cate=false){
        $title=request()->search??NULL;
        $season=request()->season??NULL;
        //$user = Util::getUserDetail();
        $typeID =  \App\Models\MediaType::where('name',str_replace('_',' ',$type))->first()?->id;
        //$client =$user;

        $wner =',"Admin" as name';

        if(!$cate){
            $cateLimit =\App\Models\AdminMediaCategory::where('media_type_id',$typeID)->get();
            //$clientMedia =new \Illuminate\Database\Eloquent\Collection;
            $mediaData =[];
            foreach($cateLimit as $cate){
                $cid =$cate->id;
                //$stdClass=new \stdClass;
                $name =$cate->category;
                $stdClass = Media::whereHas('filterMedia',function($media) use($typeID,$cid){
                    $media->where('media_filter.media_type_id',$typeID)
                    ->where('media_filter.admin_media_category_id',$cid);})
                    ->select('admin_media.*','media_types.name as category','admin_media_categories.category as subCategory'
                 ,'media_filter.season','media_filter.episode')
                 ->leftjoin('media_filter','admin_media.id','media_filter.admin_media_id')
                 ->leftjoin('media_types','media_filter.media_type_id','media_types.id')
                 ->leftjoin('admin_media_categories','media_filter.admin_media_category_id','admin_media_categories.id')
                 ->selectRaw('DATE_FORMAT(admin_media.updated_at, "%d %b %y") as date'.$wner)
                 ->withCount('comments','likes')
                 ->take(4)->paginate(15);
                 //$stdClass->category=$name;
                 if(count($stdClass)>0) $mediaData[]=$stdClass;
               // $clientMedia = $clientMedia->merge([$stdClass]);


            }


            return response()->json(['status'=>true,'message'=>'Media found','media'=>$mediaData]);


        }
        else{
        $cateID = ($cate && $cate !="all_categories")?\App\Models\AdminMediaCategory::where('media_type_id',$typeID)
        ->where('category',str_replace('_',' ',$cate))->first()?->id:null;

        if((!$typeID || ($cate && !$cateID)) && $cate !="all_categories"){

            return response()->json(['status'=>false,'message'=>"Not Valid URI"]);

        }

        /*
         $clientId =(!$clientId && $user != null || $user != null && $user->role =="client" && !$clientId)?$user->id:$clientId;
         $inputs =['id'=>$clientId];
         $rules =['id'=>'required|integer|exists:clients,id'];
         $adminMedia =false;

         $checkValid = Validator::make($inputs,$rules);
         if($checkValid->fails())
         {

           return response()->json(['status'=>false,'message'=>'ID is Not Valid Or Not Log In']);

          }

        */



         $clientMedia = Media::whereHas('filterMedia',function($media) use($typeID,$cateID,$season,$title){
            if($typeID) $media->where('media_filter.media_type_id',$typeID);
            if($cateID ) $media->where('media_filter.admin_media_category_id',$cateID);
            if($season) $media->where('media_filter.season',$season);})->select('admin_media.*','media_types.name as category','admin_media_categories.category as subCategory'
         ,'media_filter.season','media_filter.episode')
         ->leftjoin('media_filter','admin_media.id','media_filter.admin_media_id')
         ->leftjoin('media_types','media_filter.media_type_id','media_types.id')
         ->leftjoin('admin_media_categories','media_filter.admin_media_category_id','admin_media_categories.id')
         ->selectRaw('DATE_FORMAT(admin_media.updated_at, "%d %b %y") as date'.$wner)
         ->search($title)->withCount('comments','likes')
         ->paginate(15);


      return response()->json(['status'=>true,'message'=>$clientMedia->count().' media found','media'=>$clientMedia]);

       }
    }



    public function getMediaById($MediaId){
        //$user = Util::getUserDetail();

       $checkValid = Validator::make(['id'=>$MediaId],['id'=>'required|integer|exists:admin_media,id']);
       if($checkValid->fails()) return response()->json(['status'=>false,'message'=>'ID is Not Valid Or Not Public']);

       $clientMedia = Media::select('admin_media.*','media_types.name as category','admin_media_categories.category as subCategory'
     ,'media_filter.season','media_filter.episode')
     ->leftjoin('media_filter','admin_media.id','media_filter.admin_media_id')
     ->leftjoin('media_types','media_filter.media_type_id','media_types.id')
     ->leftjoin('admin_media_categories','media_filter.admin_media_category_id','admin_media_categories.id')
     ->selectRaw('DATE_FORMAT(admin_media.updated_at, "%d %b %y") as date')
     ->where('admin_media.id',$MediaId)->withCount('comments','likes')->first();

       return response()->json(['status'=>true,'message'=>'Media Details','media'=>$clientMedia]);

          }



    public function getMediaByIdTest($MediaId){
        //$user = Util::getUserDetail();

       $checkValid = Validator::make(['id'=>$MediaId],['id'=>'required|integer|exists:admin_media,id']);
       if($checkValid->fails()) return response()->json(['status'=>false,'message'=>'ID is Not Valid Or Not Public']);

       $clientMedia = Media::select('admin_media.*','media_types.name as category','admin_media_categories.category as subCategory'
     ,'media_filter.season','media_filter.episode')
     ->leftjoin('media_filter','admin_media.id','media_filter.admin_media_id')
     ->leftjoin('media_types','media_filter.media_type_id','media_types.id')
     ->leftjoin('admin_media_categories','media_filter.admin_media_category_id','admin_media_categories.id')
     ->selectRaw('DATE_FORMAT(admin_media.updated_at, "%d %b %y") as date')
     ->where('admin_media.id',$MediaId)->withCount('comments','likes')->first();

       return response()->json(['status'=>true,'message'=>'Media Details','media'=>$clientMedia]);

          }

          public function getCate($category=false){

            try{

                $record = \App\Models\MediaType::with('categories')->category(str_replace('_',' ',$category))->get();
                return response()->json(['status'=>true,'data'=>$record]);

            }
            catch(\Throwable $th){
 //return $th->getMessage();
                return response()->json(['status'=>false,'data'=>[]]);
            }
          }


          public function addSeries(Request $req){

            $rules =['title'=>'required|string|unique:series,title',
            'des'=>'required|string','subDes'=>'required|string',
            'thumbs' => 'nullable|mimes:jpeg,jpg,gif,png',
            'type'=>'required|string',
            'category'=>'required|string'];
            $validInput = Validator::make($req->all(),$rules);
            if($validInput->fails()){

                return response()->json(['status'=>false,
                'message'=>'Some fields is Not Valid','errors'=>$validInput->errors()],422);
            }

            $data =array_intersect_key($req->all(),['title'=>'','des'=>'','subDes'=>'']);
            if ($req->hasFile('thumbs')) {

                $file = $req->file('thumbs');
                $type = $file->extension();
                $fid="f1";
                $data['thumbs'] = bin2hex($fid)."_".uniqid() . '_media' . time() . '.' . $type;
                $file->storeAs("public/media/",$data['thumbs']);
                $data['thumbs']= request()->getSchemeAndHttpHost().'/storage/media/'.$data['thumbs'];
            }

            try{

                $typeID=\App\Models\MediaType::firstOrCreate(['name'=>$req->type])->id;
                $data['media_type_id']=$typeID;
                $data['admin_media_category_id']=\App\Models\AdminMediaCategory::firstOrCreate([
               'category'=>$req->category,
               'media_type_id'=>$typeID
               ])->id;

                \App\Models\Series::create($data);
                return response()->json(['status'=>true,
                'message'=>'Series Added']);
            }
            catch(\Throwable $th){
                return response()->json(['status'=>false,
                'message'=>'Fails to add Series'],500);
             }

          }




          public function updateSeries(Request $req,$id){

            $rules =[
            'SeriesId'=>'required|exists:series,id',
            'title'=>'nullable|string|unique:series,title',
            'des'=>'nullable|string',
            'subDes'=>'nullable|string',
            'thumbs' => 'nullable|mimes:jpeg,jpg,gif,png',
            'type'=>'nullable|required_with:category|string',
            'category'=>'nullable|string'];
            $validInput = Validator::make(['SeriesId'=>$id]+$req->all(),$rules);
            if($validInput->fails()){

                return response()->json(['status'=>false,
                'message'=>'Some fields is Not Valid','errors'=>$validInput->errors()],422);
            }

            $Series = \App\Models\Series::find($id);

            $data =array_intersect_key(array_filter($req->all()),['title'=>'','des'=>'','subDes'=>'']);
            if ($req->hasFile('thumbs')) {

                $thumbs =strchr($Series->thumbs, '/media');
                if ($thumbs && \File::exists(storage_path("app/public$thumbs"))) {
                     \File::delete(storage_path("app/public$thumbs"));
                     }


                $file = $req->file('thumbs');
                $type = $file->extension();
                $fid="f1";
                $data['thumbs'] = bin2hex($fid)."_".uniqid() . '_media' . time() . '.' . $type;
                $file->storeAs("public/media/",$data['thumbs']);
                $data['thumbs']= request()->getSchemeAndHttpHost().'/storage/media/'.$data['thumbs'];
            }

            try{

                if($req->type)
                {
                $typeID=\App\Models\MediaType::firstOrCreate(['name'=>$req->type])->id;
                $data['media_type_id']=$typeID;
                }
                if($req->category && $req->type)
                {
                $data['admin_media_category_id']=\App\Models\AdminMediaCategory::firstOrCreate([
               'category'=>$req->category,
               'media_type_id'=>$typeID
               ])->id;
                }

                $Series->update($data);
                return response()->json(['status'=>true,
                'message'=>'Series Updated']);
            }
            catch(\Throwable $th){
                return response()->json(['status'=>false,
                'message'=>'Fails to Update Series'],500);
             }

          }


    public function removeSeries($id){

        $idValidate= Validator::make(['seriesId'=>$id],['seriesId'=>'required|integer|exists:series,id']);

        if($idValidate->fails()){

            return response()->json(['status'=>false,'message'=>'Media ID is not Valid'],422);
        }

        try{

           $Series =\App\Models\Series::find($id);


           $seriesID=$Series->id;
           $episodesIds =\App\Models\SeriesMedia::where('series_id',$seriesID)->pluck('admin_media_id')->toArray();
            foreach($episodesIds as $mid){
            Media::find($mid)?->delete();
         }
           foreach($Series->Seasons()->get() as $season){

            $season->delete();
           }
           $thumbs =strchr($Series->thumbs, '/media');
           if (\File::exists(storage_path("app/public$thumbs"))) {
                \File::delete(storage_path("app/public$thumbs"));
                }
           $Series->delete();

            return response()->json(['status'=>true,'message'=>'Series Record Removed!']);


        }
        catch(\Throwable $th){
            return response()->json(['status'=>false,'message'=>'Fails to Remove Series'],500);
           }

    }




      public function removeSeason($id){

        $idValidate= Validator::make(['seasonId'=>$id],['seasonId'=>'required|integer|exists:series_seasons,id']);

        if($idValidate->fails()){

            return response()->json(['status'=>false,'message'=>'Media ID is not Valid'],422);
        }

        try{

           $Season =\App\Models\SeriesSeason::find($id);
           $seriesID=$Series->series_id;
           $episodesIds =\App\Models\SeriesMedia::where('series_id',$seriesID)->where('season_id',$Season->id)->pluck('admin_media_id')->toArray();
            foreach($episodesIds as $mid){
            Media::find($mid)?->delete();
         }

           $thumbs =strchr($Season->thumbs, '/media');
           if (\File::exists(storage_path("app/public$thumbs"))) {
                \File::delete(storage_path("app/public$thumbs"));
                }
           $Season->delete();

            return response()->json(['status'=>true,'message'=>'Season Record Removed!']);


        }
        catch(\Throwable $th){
            return response()->json(['status'=>false,'message'=>'Fails to Remove Season'],500);
           }

    }



          public function addSeriesSeason(Request $req){

            $rules =['title'=>'required|string|unique:series_seasons,title',
            'des'=>'required|string',
            'subDes'=>'required|string',
            'thumbs' => 'nullable|mimes:jpeg,jpg,gif,png',
            'series_id'=>'required|integer|exists:series,id',
            'season'=>'required|integer'];
            $validInput = Validator::make($req->all(),$rules);
            if($validInput->fails()){

                return response()->json(['status'=>false,
                'message'=>'Some fields is Not Valid','errors'=>$validInput->errors()],422);
            }

            $data =array_intersect_key($req->all(),['title'=>'','des'=>'','subDes'=>'','series_id'=>'','season'=>'']);
            if ($req->hasFile('thumbs')) {

                $file = $req->file('thumbs');
                $type = $file->extension();
                $fid="f1";
                $data['thumbs'] = bin2hex($fid)."_".uniqid() . '_media' . time() . '.' . $type;
                $file->storeAs("public/media/",$data['thumbs']);
                $data['thumbs']= request()->getSchemeAndHttpHost().'/storage/media/'.$data['thumbs'];
            }

            try{
                \App\Models\SeriesSeason::create($data);
                return response()->json(['status'=>true,
                'message'=>'Season Added']);
            }
            catch(\Throwable $e){
                $errorCode = $e->errorInfo[1];
                if ($errorCode == 1062)
                return response()->json(['status' =>false, 'message' =>"Season {$req->season} Allready Exists"]);

                return response()->json(['status'=>false,
                'message'=>'Fails to add Season'],500);
             }

          }




        public function updateSeason(Request $req,$id){

            $rules =[
            'seasonId'=>'required|integer|exists:series_seasons,id',
            'title'=>'nullable|string|unique:series_seasons,title',
            'des'=>'nullable|string',
            'subDes'=>'nullable|string',
            'thumbs' => 'nullable|mimes:jpeg,jpg,gif,png',
            'series_id'=>'nullable|integer|exists:series,id',
            'season'=>'nullable|integer'];
            $validInput = Validator::make(['seasonId'=>$id]+$req->all(),$rules);
            if($validInput->fails()){

                return response()->json(['status'=>false,
                'message'=>'Some fields is Not Valid','errors'=>$validInput->errors()],422);
            }

            $Season= \App\Models\SeriesSeason::find($id);

            $data =array_intersect_key(array_filter($req->all()),['title'=>'','des'=>'','subDes'=>'','series_id'=>'','season'=>'']);
            if ($req->hasFile('thumbs')) {

                $file = $req->file('thumbs');
                $type = $file->extension();
                $fid="f1";
                $data['thumbs'] = bin2hex($fid)."_".uniqid() . '_media' . time() . '.' . $type;
                $file->storeAs("public/media/",$data['thumbs']);
                $data['thumbs']= request()->getSchemeAndHttpHost().'/storage/media/'.$data['thumbs'];
            }

    try{
                $Season->update($data);
                return response()->json(['status'=>true,
                'message'=>'Season Updated']);


            }
            catch(\Throwable $e){
                $errorCode = $e->errorInfo[1];
                if ($errorCode == 1062)
                return response()->json(['status' =>false, 'message' =>"Season {$req->season} Allready Exists"]);

                return response()->json(['status'=>false,
                'message'=>'Fails to Update Season'],500);
             }

          }

          public function fetchSeries(Request $req){

            $series =\App\Models\Series::paginate(15);

            return response()->json(['status'=>true,
            'message'=>"{count($series)} Series In Page",'series'=> $series]);

          }

          public function getSeries($id){

            $series =\App\Models\Series::find(intval($id));
            if(!$series)   return response()->json(['status'=>false,
            'message'=>"May be Series Id Not Valid"]);


            return response()->json(['status'=>true,
            'message'=>"Series details",'series'=> $series]);

          }


          public function fetchSeasons($seriesID,$season=false,$episode=false,$cateID=false,$title=false){
            //admin_media_id


            if(!$season){

                $seasons = \App\Models\Series::where('id',$seriesID)->with('Seasons')->get();

            }
            else{


                $sn =$season;
                $season =\App\Models\SeriesSeason::where('season',$season)->where('series_id',$seriesID)->value('id');
                if(!$season) return response()->json(['status'=>false,'message'=>"Season $sn Not Found"]);



            $seasons = Media::whereHas('filterSeries',function($media) use($seriesID,$season,$episode,$cateID,$title){
               // if($typeID) $media->where('media_filter.media_type_id',$typeID);
                //if($cateID ) $media->where('media_filter.admin_media_category_id',$cateID);
                //if($season) $season =\App\Models\SeriesSeason::where('season',$season)->where('series_id',$seriesID)->value('id');

                ($season)?$media->where('series_media.season',$season):$media->where('series_media.episode',1);
                if($seriesID) $media->where('series_media.series_id',$seriesID);
                if($episode) $media->where('series_media.episode',$episode);

            })->select('admin_media.*','media_types.name as category',
            'admin_media_categories.category as subCategory'
             ,'series_media.season','series_media.episode')
             ->leftjoin('series_media','admin_media.id','series_media.admin_media_id')
             ->leftjoin('media_types','series_media.media_type_id','media_types.id')
             ->leftjoin('admin_media_categories','series_media.admin_media_category_id','admin_media_categories.id')
             ->selectRaw('DATE_FORMAT(admin_media.updated_at, "%d %b %y") as date')
             ->search($title)->withCount('comments','likes')
             ->paginate(15);
        }

             return response()->json(['status'=>true,'message'=>$seasons->count().' seasons','seasons'=>$seasons]);


          }



           public function getNewTrailer(){

                //$user = Util::getUserDetail();

               $trailer = Media::select('admin_media.*','media_types.name as category','admin_media_categories.category as subCategory'
             ,'media_filter.season','media_filter.episode')
             ->whereHas('filterMedia',function($media){
                $media->where('media_filter.media_type_id',\App\Models\MediaType::where('name','trailers')->value('id'));})
             ->leftjoin('media_filter','admin_media.id','media_filter.admin_media_id')
             ->leftjoin('media_types','media_filter.media_type_id','media_types.id')
             ->leftjoin('admin_media_categories','media_filter.admin_media_category_id','admin_media_categories.id')
             ->selectRaw('DATE_FORMAT(admin_media.updated_at, "%d %b %y") as date')
             ->latest()->first();
               return response()->json(['status'=>true,'message'=>'Trailer Details','trailer'=>$trailer]);

                  }


                  public function fetchSeriesPage($type,$cate=false){


                    $typeID =  \App\Models\MediaType::where('name',str_replace('_',' ',$type))->first()?->id;
                    //$client =$user;

                    $wner =',"Admin" as name';

                        $mediaData =[];

                        if(!$cate)
                        {
                        $cateLimit =\App\Models\AdminMediaCategory::where('media_type_id',$typeID)->get();
                        //$clientMedia =new \Illuminate\Database\Eloquent\Collection;

                        foreach($cateLimit as $cate){
                            $cid =$cate->id;
                            //$stdClass=new \stdClass;
                            $name =$cate->category;
                            $series =\App\Models\Series::where('admin_media_category_id',$cid)->select('series.*')
                            ->selectRaw("'$name' as category")->paginate(15);
                            if(count($series)>0) $mediaData[]=$series;

                        }
                    }
                    else{
                        $cate =\App\Models\AdminMediaCategory::where('media_type_id',$typeID)->where('category',$cate)->first();

                        $name =$cate->category;
                        $series =\App\Models\Series::where('admin_media_category_id',$cate->id)->select('series.*')
                        ->selectRaw("'$name' as category")->paginate(15);
                        if(count($series)>0) $mediaData[]=$series;

                    }


                        return response()->json(['status'=>true,'message'=>'Series List','series'=>$mediaData]);


                  }

}
