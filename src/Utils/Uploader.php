<?php
namespace Lfalmeida\Lbase\Utils;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
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
     * @return bool
     */
    public function handleMultipleFiles(array $files)
    {
        foreach ($files as $file) {
            $this->handleSingleFile($file);
        }
        return true;
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

        $d = new Document();

        $d->uniqueName = $this->getUniqueName();
        $d->originalName = $file->getClientOriginalName();
        $d->extension = $file->getClientOriginalExtension();
        $d->mimeType = $file->getClientMimeType();
        $d->filePath = sprintf('%s/%s.%s', $this->subfolder, $d->uniqueName, $d->extension);
        $d->storage = Config::get('filesystems.default');
        $d->hash = md5_file($file->getRealPath());
        $d->createdAt = date('c');

        $disk = Storage::disk()->getDriver();

        $disk->put($d->filePath, fopen($file, 'r+'), [
            'visibility' => 'public',
            'ContentType' => $d->mimeType
        ]);

        $d->url = Storage::url($d->filePath);

        $this->addFileToList($d);

        return $d;
    }

    /**
     * @return string
     */
    private function getUniqueName()
    {
        return (string)str_replace([' ', ':'], '-', Carbon::now()->toDateTimeString());
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
     * @param $filePath
     *
     * @return mixed
     */
    public function removeFile($filePath)
    {
        return Storage::disk()->delete($filePath);
    }

    /**
     * @return array
     */
    public function getUploadedFilesList()
    {
        return $this->fileList;
    }


}