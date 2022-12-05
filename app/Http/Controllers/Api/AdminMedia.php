<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdminMedia as Media;
use App\Http\Controllers\Api\Tools\Util;
use Validator;
class AdminMedia extends Controller
{

    public function addMedia(Request $req, $clientId = false)
    {
        $this->user = Util::getUserDetail();

        preg_match_all("/movies|trailers|series/i", $req->type, $dummy);

        $mainType = $dummy[0];
        $mainType = (!isset($mainType[0]))?'Videos':$mainType[0];

        $client = ($this->user->role == 'admin' && $clientId) ?
            Client::find($clientId) :
             $user = ($this->user->role == 'admin')? $this->user:Client::find($this->user->id);

            if ($this->user->role != 'admin' && $client->media()->count()>3) {
                return response()->json(['status' => false, 'message' => 'Only 4 Media Files Can Be Added!']);
            }
        $maxSize =($req->type == 'trailers')?"":"|max:21024";
        $maxDur =($req->type == 'trailers')?"":"|max:30";
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
             $typeID=\App\Models\MediaType::firstOrCreate(['name'=>$req->type])->id;
             $filter =['media_type_id'=>$typeID
             ,'admin_media_category_id'=>\App\Models\AdminMediaCategory::firstOrCreate([
             'category'=>$req->category,
             'media_type_id'=>$typeID
             ])->id];
             if(stripos($req->type,"series")!==false){
                $rule=array_merge( $rule, ['season'=>'required|integer',
                    'episode'=>'required|integer'
                   ]);
                 $filter['season']=$req->season;
                 $filter['episode']=$req->episode;
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


        $checkValid = Validator::make($req->all(), $rule);

        if ($checkValid->fails()) {

            return response()->json(['status' => false, 'message' => 'Invalid Data', 'errors' => $checkValid->errors()],422);
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
            return response()->json(['status' =>true, 'message' => 'Media Added!']);
        } catch (\illuminate\Database\QueryException $e) {
            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    }



    public function fetchAllMedia($type,$cate=false){
        $title=request()->search??NULL;
        $season=request()->season??NULL;
        //$user = Util::getUserDetail();
        $typeID =  \App\Models\MediaType::where('name',str_replace('_',' ',$type))->first()?->id;
        //$client =$user;
         
        $wner =',"Admin" as name';

        if(!$cate){
            $cateLimit =\App\Models\AdminMediaCategory::where('media_type_id',$typeID)->take(5)
            ->get();
            $clientMedia =new \Illuminate\Database\Eloquent\Collection;

            foreach($cateLimit as $cate){
                $cid =$cate->id;
                $clientMedia = $clientMedia->merge(Media::whereHas('filterMedia',function($media) use($typeID,$cid){
                    $media->where('media_filter.media_type_id',$typeID)
                    ->where('media_filter.admin_media_category_id',$cid);})
                    ->select('admin_media.*','media_types.name as category','admin_media_categories.category as subCategory'
                 ,'media_filter.season','media_filter.episode')
                 ->leftjoin('media_filter','admin_media.id','media_filter.admin_media_id')
                 ->leftjoin('media_types','media_filter.media_type_id','media_types.id')
                 ->leftjoin('admin_media_categories','media_filter.admin_media_category_id','admin_media_categories.id')
                 ->selectRaw('DATE_FORMAT(admin_media.updated_at, "%d %b %y") as date'.$wner)
                 ->take(4)->get());


            }

            return response()->json(['status'=>true,'message'=>$clientMedia->count().' media found','media'=>$clientMedia]);
   

        }
        else{
        $cateID = ($cate)?\App\Models\AdminMediaCategory::where('media_type_id',$typeID)
        ->where('category',str_replace('_',' ',$cate))->first()?->id:null;

        if(!$typeID || ($cate && !$cateID)){

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
         ->search($title)
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
     ->where('admin_media.id',$MediaId)->first();
       return response()->json(['status'=>true,'message'=>'Media Details','media'=>$clientMedia]);

          }

          public function getCate(){

            try{

                $record = \App\Models\MediaType::with('categories')->get();
                return response()->json(['status'=>true,'data'=>$record]);

            }
            catch(\Throwable $th){
 //return $th->getMessage();
                return response()->json(['status'=>false,'data'=>[]]);
            }
          }


}
