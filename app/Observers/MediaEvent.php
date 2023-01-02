<?php

namespace App\Observers;

use App\Models\adminMedia as admin_media;

class MediaEvent
{
    /**
     * Handle the admin_media "created" event.
     *
     * @param  \App\Models\admin_media  $admin_media
     * @return void
     */
    public function created(admin_media $admin_media)
    {
        //
    }

    /**
     * Handle the admin_media "updated" event.
     *
     * @param  \App\Models\admin_media  $admin_media
     * @return void
     */
    public function updated(admin_media $admin_media)
    {
    
        if($admin_media->isDirty('thumbs')){
            $old_thumbs =strchr($admin_media->getOriginal('thumbs'), '/media');
                if (\File::exists(storage_path("app/public$old_thumbs"))) {
                     \File::delete(storage_path("app/public$old_thumbs"));
                     }
                   }

                   if($admin_media->isDirty('url')){
                    $old_media =strchr($admin_media->getOriginal('url'), '/media');
                        if (\File::exists(storage_path("app/public$old_media"))) {
                             \File::delete(storage_path("app/public$old_media"));
                             }
                           }
      }

    /**
     * Handle the admin_media "deleted" event.
     *
     * @param  \App\Models\admin_media  $admin_media
     * @return void
     */


    public function deleted(admin_media $admin_media)
    {
       
        
        $old_thumbs =strchr($admin_media->getOriginal('thumbs'), '/media');
        if (\File::exists(storage_path("app/public$old_thumbs"))) {
             \File::delete(storage_path("app/public$old_thumbs"));
             }
           

        $old_media =strchr($admin_media->getOriginal('url'), '/media');
           if (\File::exists(storage_path("app/public$old_media"))) {
                \File::delete(storage_path("app/public$old_media"));
                }

    }

    /*
     * Handle the admin_media "restored" event.
     *
     * @param  \App\Models\admin_media  $admin_media
     * @return void
     
    public function restored(admin_media $admin_media)
    {
        //
    }


     * Handle the admin_media "force deleted" event.
     *
     * @param  \App\Models\admin_media  $admin_media
     * @return void
     
    public function forceDeleted(admin_media $admin_media)
    {
        //
    }
    */
}
