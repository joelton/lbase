<?php
namespace Lfalmeida\Lbase\Utils;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Lfalmeida\Lbase\Models\Document;

/**
 * Class Uploader
 *
 * @package Lfalmeida\Lbase\Utils
 *
 */
class Uploader
{

    public $subfolder;
    private $fileList = [];

    /**
     * Uploader constructor.
     *
     * @param string $subfolder
     */
    public function __construct($subfolder = '')
    {
        $this->subfolder = rtrim($subfolder, '/');
    }

    /**
     * @param $files
     *
     * @return bool|Document
     * @throws \Exception
     */
    public function handle($files)
    {
        if (is_array($files)) {
            return $this->handleMultipleFiles($files);
        }
        if (is_object($files)) {
            return $this->handleSingleFile($files);
        }
        throw new \Exception("Dados inválidos para upload");
    }

    /**
     * @param array $files
     *
     * @return array
     */
    public function handleMultipleFiles(array $files)
    {
        foreach ($files as $file) {
            $this->handleSingleFile($file);
        }
        return $this->getUploadedFilesList();
    }

    /**
     * @param $file
     *
     * @return Document
     * @throws \Exception
     */
    public function handleSingleFile($file)
    {
        if (!$file instanceof UploadedFile) {
            throw new \Exception("Dados inválidos para upload");
        }

        $document = $this->createDocument($file);

        $this->processBeforeSave($document);

        $disk = Storage::disk()->getDriver();
        $disk->put($document->filePath, fopen($file, 'r+'), [
            'visibility' => 'public',
            'ContentType' => $document->mimeType
        ]);

        $document->url = config('filesystems.default') == 'public' ? asset('images/' . $document->filePath) : Storage::url($document->filePath);
        $document->save();

        $this->addFileToList($document);

        return $document;
    }

    /**
     * @param $file
     *
     * @return Document
     */
    private function createDocument($file)
    {
        $d = new Document();
        $d->uniqueName = $this->getUniqueName();
        $d->originalName = $file->getClientOriginalName();
        $d->extension = strtolower($file->getClientOriginalExtension());
        $d->mimeType = $file->getClientMimeType();
        $d->filePath = sprintf('%s/%s.%s', $this->subfolder, $d->uniqueName, $d->extension);
        $d->storage_disk = Config::get('filesystems.default');
        $d->hash = md5_file($file->getRealPath());
        $d->realPath = $file->getRealPath();

        return $d;
    }

    /**
     * @return string
     */
    private function getUniqueName()
    {
        return (string)uniqid();
    }

    /**
     * @param Document $document
     */
    private function processBeforeSave(Document $document)
    {
        if ($document->isImage()) {

            $maxWidth = config('upload.maxImageSizes.width');
            $maxHeight = config('upload.maxImageSizes.height');

            $img = Image::make($document->realPath);
            $img->resize($maxWidth, $maxHeight, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $img->save(sprintf('%s.%s', $document->realPath, $document->extension), 90);
        }
    }

    /**
     * @param Document $document
     *
     */
    public function addFileToList(Document $document)
    {
        $this->fileList[] = $document;
    }

    /**
     * @return array
     */
    public function getUploadedFilesList()
    {
        return $this->fileList;
    }

    /**
     * @param $filePath
     *
     * @return mixed
     */
    public function removeFile($filePath)
    {
        return Storage::disk()->delete($filePath);
    }


}
