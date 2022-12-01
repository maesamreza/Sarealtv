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

        return $mainType;
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
             $filter =['media_type_id'=>$data['type']
             ,'admin_media_category_id'=>\App\Models\AdminMediaCategory::firstOrCreate([
             'category'=>$req->category,
             'media_type_id'=>\App\Models\MediaType::firstOrCreate(['name'=>$req->type])->id
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

        return $data;
        try {
            $media = $client->media()->create($data);
            
            $media->filterMedia()->attach([$media->id=> $filter]);
            return response()->json(['status' =>true, 'message' => 'Media Added!']);
        } catch (\illuminate\Database\QueryException $e) {
            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    }



    public function fetchAllMedia($type,$cate=false,$season=false,$title=false){
        $user = Util::getUserDetail();
        $typeID =  \App\Models\MediaType::where('name',str_replace('_',' ',$type))->first()?->id;
        if(!$typeID){

            return response()->json(['status'=>false,'message'=>"$type Not Valid"]);
         
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
   
         $client =$user;
         
         $wner =',"'.$client->name.'" as name';
        //  $wner .='"'.$client->clientProfile->picture.'" as picture,';
        //  $wner .='"'.$client->clientProfile->gender.'" as gender,'; ($typeID,$cate,$season,$title
        //  $wner .='"'.$client->clientProfile->account_type.'" as account_type';
        //$Media=new Media;
         $clientMedia = Media::whereHas('filterMedia',function($media) use($typeID,$cate,$season,$title){
            if($typeID) $media->where('media_filter.media_type_id',$typeID);
            if($cate) $media->where('media_filter.admin_media_category_id',$cate);
            if($season) $media->where('media_filter.season',$season);
            if($title) $media->where('admin_media.title','LIKE',"%%$title%%");

         })->select('admin_media.*')
         ->selectRaw('DATE_FORMAT(admin_media.updated_at, "%d %b %y") as date'.$wner)
         ->get();//->paginate(15);
   
    return $clientMedia;
      return response()->json(['status'=>true,'message'=>$clientMedia->count().' media found','media'=>$clientMedia]);
   
       }
   
   


}
