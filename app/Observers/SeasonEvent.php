<?php

namespace App\Observers;

use App\Models\SeriesSeason;

class SeasonEvent
{
    /**
     * Handle the SeriesSeason "created" event.
     *
     * @param  \App\Models\SeriesSeason  $seriesSeason
     * @return void
     */
    public function created(SeriesSeason $seriesSeason)
    {
        //
    }

    /**
     * Handle the SeriesSeason "updated" event.
     *
     * @param  \App\Models\SeriesSeason  $seriesSeason
     * @return void
     */
    public function updated(SeriesSeason $seriesSeason)
    {
    

        if($seriesSeason->isDirty('thumbs')){
            $old_thumbs =strchr($seriesSeason->getOriginal('thumbs'), '/media');
                if (\File::exists(storage_path("app/public$old_thumbs"))) {
                     \File::delete(storage_path("app/public$old_thumbs"));
                     }
                   }
    }

    /**
     * Handle the SeriesSeason "deleted" event.
     *
     * @param  \App\Models\SeriesSeason  $seriesSeason
     * @return void
     */
    public function deleted(SeriesSeason $seriesSeason)
    {
    
        $old_thumbs =strchr($seriesSeason->getOriginal('thumbs'), '/media');
        if (\File::exists(storage_path("app/public$old_thumbs"))) {
             \File::delete(storage_path("app/public$old_thumbs"));
             }
           
    }

    /*
     * Handle the SeriesSeason "restored" event.
     *
     * @param  \App\Models\SeriesSeason  $seriesSeason
     * @return void
    public function restored(SeriesSeason $seriesSeason)
    {
        //
    }

    /**
     * Handle the SeriesSeason "force deleted" event.
     *
     * @param  \App\Models\SeriesSeason  $seriesSeason
     * @return void
    
    public function forceDeleted(SeriesSeason $seriesSeason)
    {
        //
    }

    */
}
