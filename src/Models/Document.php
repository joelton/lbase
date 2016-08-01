<?php
namespace Lfalmeida\Lbase\Models;

/**
 * Class Document
 *
 * Classe simples para documentos
 *
 * @package Lfalmeida\Lbase\Models
 */
class Document extends BaseModel
{

    protected $hidden = [
//        'id',
//        'unique_name',
//        'original_name',
//        'extension',
//        'mime_type',
//        'storage_disk',
//        'hash',
//        'file_path',
//        'meta',
//        'created_at',
//        'updated_at',
    ];

    public function isImage()
    {
        return in_array($this->attributes['mime_type'], config('upload.allowedMimeTypes.images'));
    }


}