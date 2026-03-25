<?php

namespace App\Support\Media;

use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ActividadPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        $model = $media->model;

        if ($model && $model->departamental_id) {
            return 'departamentales/' 
                . $model->departamental_id 
                . '/actividades/' 
                . $model->id . '/';
        }

        return 'otros/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media) . 'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . 'responsive/';
    }
}