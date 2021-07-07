<?php

namespace App\Http\Traits;

use User\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait FileUploadTrait
{

    /**
     * File upload trait used in controllers to upload files
     * @param Request $request
     * @param array $valid_indices
     * @param Model|null $model
     * @param array $valid_mime_types
     * @param array $except
     * @param string $valid_expression
     * @param string $path
     * @return Mixed
     *
     */
    public function saveFiles(Request $request, array $valid_indices = [], Model $model = null, array $valid_mime_types = [], array $except = [], $valid_expression = "",$path = 'uploads')
    {
        ini_set('memory_limit', '-1');
        $finalRequest = $request;

        foreach ($request->all() as $key => $value) {
            if ($request->hasFile($key)) {
                if (!in_array($key, $except))
                    if (count($valid_mime_types) == 0 || in_array($request->file($key)->getClientMimeType(), $valid_mime_types))
                        if (count($valid_indices) == 0 || in_array($key, $valid_indices) ) {
                            if(empty($valid_expression) || Str::startsWith($key,$valid_expression)){
                                $extension = array_last(explode('.', $request->file($key)->getClientOriginalName()));
                                $name = array_first(explode('.', $request->file($key)->getClientOriginalName()));
                                $filename = time() . '-' . str_slug($name) . '.' . $extension;
                                Storage::disk($path)->put($filename, File::get($request->file($key)));


                                $size = $request->file($key)->getSize() / 1024;
                                if ($model) {
                                    Media::create([
                                        'model_type' => get_class($model),
                                        'model_id' => $model->id,
                                        'name' => $filename,
                                        'type' => $request->file($key)->getClientMimeType(),
                                        'file_name' => $filename,
                                        'size' => $size,
                                    ]);
                                    return true;
                                } else
                                    return  new Request(array_merge($finalRequest->all(), [$key => $filename]));
                            }


                        }
            }
        }
        return false;
    }


}
