<?php



use Illuminate\Support\Facades\Cache;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Support\Facades\Storage;

if (!function_exists('isArabic')) {
    function isArabic(): bool
    {
        return app()->getLocale() == "ar";
    }
}

if (!function_exists('getDirection')) {

    function getDirection()
    {
        return isArabic() ? "rtl" : 'ltr';
    }
}

if (!function_exists('isDarkMode')) {

    function isDarkMode(): bool
    {
        return session('theme_mode') === "dark";
    }
}

if (!function_exists('uploadImageToDirectory')) {

    function uploadImageToDirectory($imageFile, $model = '')
    {
        $model     = Str::plural($model);
        $model     = Str::ucfirst($model);
        $path      = "/Images/$model";
        $imageName = str_replace(' ', '', 'lms_' . time() . $imageFile->getClientOriginalName());  // Set Image name
        $imageFile->storeAs($path, $imageName, 'public');
        return $imageName;
    }
}
if (!function_exists('uploadAttachmentToDirectory')) {
    function uploadAttachmentToDirectory($file, $model = '')
    {
        $model     = Str::plural($model);
        $model     = Str::ucfirst($model);
        $path      = "attachments/$model";
        $fileName  = str_replace(' ', '', 'lms_' . time() . '_' . $file->getClientOriginalName());

        $file->storeAs($path, $fileName, 'public');

        return $fileName;
    }
}


if (!function_exists('uploadAudioToDirectory')) {

    function uploadAudioToDirectory($audioFile, $model = '')
    {
        $model     = Str::plural($model);
        $model     = Str::ucfirst($model);
        $path      = "/Audio/$model"; // Folder structure: storage/app/public/Audio/Models
        $audioName = str_replace(' ', '', 'lms_' . time() . $audioFile->getClientOriginalName()); // Unique name
        $audioFile->storeAs($path, $audioName, 'public');
        return $audioName; // Just the filename
    }
}

if(!function_exists('convertToYoutubeEmbed')){
    function convertToYoutubeEmbed($url)
{
    preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|v\/|.*[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches);
    if (isset($matches[1])) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }
    return null; // or return $url as fallback
}

}

if (!function_exists('updateModelImage')) {

    function updateModelImage($model, $imageFile, $directory)
    {
        deleteImageFromDirectory($model->image, $directory);
        return uploadImageToDirectory($imageFile, $directory);
    }
}


if (!function_exists('formatNumber')) {
    function formatNumber($number)
    {
        if ($number >= 1000 && $number < 1000000) {
            return number_format($number / 1000, 3);
        } elseif ($number >= 1000000) {
            return number_format($number / 1000000, 3);
        } else {
            return $number;
        }
    }
}

if (!function_exists('deleteImageFromDirectory')) {

    function deleteImageFromDirectory($imageName, $model)
    {
        $model = Str::plural($model);
        $model = Str::ucfirst($model);

        if ($imageName != 'default.png') {
            $path = "/Images/" . $model . '/' . $imageName;
            Storage::disk('public')->delete($path);
        }
    }
}


if (!function_exists('getImagePathFromDirectory')) {

    function getImagePathFromDirectory($imageName = null, $directory = null, $defaultImage = 'default.svg')
    {
        $directory = Str::plural($directory);
        $directory = Str::ucfirst($directory);

        $imagePath         = "/storage/Images/$directory/$imageName";
        $callbackImagePath = "placeholder_images/$directory/$defaultImage";

        if ($imageName && $directory && file_exists(public_path($imagePath)))
            return asset($imagePath);
        else if (file_exists($callbackImagePath))
            return asset($callbackImagePath);
        else
            return asset("/placeholder_images/$defaultImage");
    }
}

if (!function_exists('deleteAttachmentFromDirectory')) {
    function deleteAttachmentFromDirectory($fileName, $model)
    {
        $model = Str::plural($model);
        $model = Str::ucfirst($model);

        if (!empty($fileName)) {
            $path = "attachments/$model/$fileName";
            Storage::disk('public')->delete($path);
        }
    }
}

if (!function_exists('getAttachmentPathFromDirectory')) {
    function getAttachmentPathFromDirectory($fileName = null, $directory = null)
    {
        $directory = Str::plural($directory);
        $directory = Str::ucfirst($directory);

        if ($fileName && $directory && Storage::disk('public')->exists("attachments/$directory/$fileName")) {
            return asset("storage/attachments/$directory/$fileName");
        }

        return null;
    }
}



if (!function_exists('getAudioPathFromDirectory')) {

    function getAudioPathFromDirectory($audioName = null, $directory = null, $defaultAudio = 'default.mp3')
    {
        $directory = Str::plural($directory);
        $directory = Str::ucfirst($directory);

        $audioPath         = "/storage/Audio/$directory/$audioName";
        $fallbackAudioPath = "placeholder_audio/$directory/$defaultAudio";

        if ($audioName && $directory && file_exists(public_path($audioPath))) {
            return asset($audioPath);
        } elseif (file_exists(public_path($fallbackAudioPath))) {
            return asset($fallbackAudioPath);
        } else {
            return asset("/placeholder_audio/$defaultAudio");
        }
    }

}



if (!function_exists('isTabActive')) {

    function isTabActive($path)
    {
        if (request()->routeIs($path))
            return 'active';
    }
}


if (!function_exists('isTabOpen')) {

    function isTabOpen($path)
    {

        if (request()->segment(2) === $path)
            return 'menu-item-open';
    }
}


if (!function_exists('getClassIfUrlContains')) {
    function getClassIfUrlContains($class, $word)
    {

        if ($word == "/" && count(request()->segments()) == 1)
            return $class;

        return in_array($word, request()->segments()) ? $class : '';
    }
}


if (!function_exists('abilities')) {
    function abilities()
    {
        if (is_null(cache()->get('abilities'))) {
            $abilities = Cache::remember('abilities', 60, function () {
                return auth('admin')->user()->abilities();
            });
        } else {
            $abilities = cache()->get('abilities');
        }


        return $abilities;
    }
}

if (!function_exists('getFullPathOfImagesFromDirectory')) {
    function getFullPathOfImagesFromDirectory($images, $directory)
    {
        $updatedImages = [];
        foreach ($images as $image) {
            array_push($updatedImages, getImagePathFromDirectory($image, $directory));
        }

        return $updatedImages;
    }
}

if (!function_exists('getRelationWithColumns')) {

    function getRelationWithColumns($relations): array
    {
        $relationsWithColumns = [];

        foreach ($relations as $relation => $columns) {
            array_push($relationsWithColumns, $relation . ":" . implode(",", $columns));
        }

        return $relationsWithColumns;
    }
}

if (!function_exists('getDateRangeArray')) { // takes 'Y-m-d - Y-m-d' and returns [ Y-m-d 00:00:00 , Y-m-d 23:59:59 ]

    function getDateRangeArray($dateRange): array
    {
        $dateRange = explode(' - ', $dateRange);

        return [$dateRange[0] . ' 00:00:00', $dateRange[1] . ' 23:59:59'];
    }
}

if (!function_exists('getModelData')) {

    function getModelData(Model $model, $relations = [], $orsFilters = [], $andsFilters = [], $searchingColumns = null, $onlyTrashed = false): array
    {

        $columns              = $searchingColumns ?? $model->getConnection()->getSchemaBuilder()->getColumnListing($model->getTable());
        $relationsWithColumns = getRelationWithColumns($relations); // this fn takes [ brand => [ id , name ] ] then returns : brand:id,name to use it in with clause

        /** Get the request parameters **/
        $params = request()->all();

        // set passed filters from controller if exist
        if (!$onlyTrashed)
            $model = $model->query()->with($relationsWithColumns);
        else
            $model = $model->query()->onlyTrashed()->with($relationsWithColumns);


        /** Get the count before search **/
        $itemsBeforeSearch = $model->count();

        // general search
        if (isset($params['search']['value'])) {

            if (str_starts_with($params['search']['value'], '0'))
                $params['search']['value'] = substr($params['search']['value'], 1);

            /** search in the original table **/
            foreach ($columns as $column)
                array_push($orsFilters, [$column, 'LIKE', "%" . $params['search']['value'] . "%"]);
        }

        // filter search
        if ($itemsBeforeSearch == $model->count()) {

            $searchingKeys = collect($params['columns'])->transform(function ($entry) {

                return $entry['search']['value'] != null && $entry['search']['value'] != 'all' ? Arr::only($entry, ['data', 'name', 'search']) : null; // return just columns which have search values

            })->whereNotNull()->values();


            /** if request has filters like status **/
            if ($searchingKeys->count() > 0) {

                /** search in the original table **/
                foreach ($searchingKeys as $column) {
                    if (!($column['name'] == 'created_at' or $column['name'] == 'date'))
                        array_push($andsFilters, [$column['name'], '=', $column['search']['value']]);
                    else {
                        if (!str_contains($column['search']['value'], ' - ')) // if date isn't range ( single date )
                            $model->orWhereDate($column['name'], $column['search']['value']);
                        else
                            $model->orWhereBetween($column['name'], getDateRangeArray($column['search']['value']));
                    }
                }
            }
        }

        $model = $model->where(function ($query) use ($orsFilters) {
            foreach ($orsFilters as $filter)
                $query->orWhere([$filter]);
        });

        if ($andsFilters)
            $model->where($andsFilters);

        if (isset($params['order'][0])) {
            $model->orderBy($params['columns'][$params['order'][0]['column']]['data'], $params['order'][0]['dir']);
        }

        $response = [
            "recordsTotal" => $model->count(),
            "recordsFiltered" => $model->count(),
            'data' => $model->skip($params['start'])->take($params['length'])->get()
        ];

        return $response;
    }
}

if (!function_exists('generateRandomCode')) {
    function generateRandomCode($length)
    {
        $allCharacters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $allCharacters[rand(0, strlen($allCharacters) - 1)];
        }

        return $code;
    }
}


