<?php

namespace App\Helpers;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class UploadHelper
{
    protected static array $extensiones = [
        "images" => ["jpg", "png", "jpeg"],
        "pdf" => ["pdf"],
    ];

    /**
     * Global function to attach files to a model
     * @param \Illuminate\Support\Collection|array $oldFiles
     * @param array<int, \Illuminate\Http\UploadedFile>|array<int, \Illuminate\Http\UploadedFile> $archivos
     * @param string $path
     * @param array $whereInsert tendra el nombre de la columna y el valor a insertar
     */
    public static function adjuntar_archivos(Collection|array $oldFiles = [], ?array $archivos = [], string $path, array $whereInsert = [], string $columnName = "foto"): array
    {
        $inserts = [];
        /**
         * @var array<int, \Illuminate\Http\UploadedFile> $archivos
         * @var \Illuminate\Http\UploadedFile $foto
         */
        if (empty($archivos)) {
            return $inserts;
        }
        foreach ($archivos as $archivo) {
            $name = "{$path}/" . $archivo->getClientOriginalName();
            // Ignora si el archivo ya existe
            if (in_array($name, $oldFiles)) {
                continue;
            }
            $extension = $archivo->getClientOriginalExtension();
            $archivoComprimido = null;
            // Si es una imagen, comprimirla y guardarla
            if (in_array($extension, self::$extensiones["images"])) {
                $archivoComprimido = ImageHelper::compressImageAndSave($archivo, public_path("{$path}"));
            } else {
                $imagenName = uniqid("FILE-") . time() . "." . $archivo->getClientOriginalExtension();
                // para evitar errores
                $newpath = str_replace("storage/", "", $path);
                $archivo->storeAs($newpath, $imagenName, 'public');
                $archivoComprimido = $imagenName;
            }
            $relativePath = "{$path}/{$archivoComprimido}";
            $inserts[] = array_merge($whereInsert, [
                $columnName => $relativePath,
            ]);
        }
        return $inserts;
    }
    public static function adjuntar_archivo(UploadedFile $archivo, string $path, ?string $oldPath = null, ?Model $model = null, string $columnName = "foto", )
    {
        $extension = $archivo->getClientOriginalExtension();
        $archivoComprimido = null;
        $fileName = $path . "/" . $archivo->getClientOriginalName();
        if ($oldPath && $oldPath === $fileName) {
            return $oldPath;
        }
        if (in_array($extension, self::$extensiones["images"])) {
            $archivoComprimido = ImageHelper::compressImageAndSave($archivo, public_path("{$path}"));
        } else {
            $imagenName = uniqid("FILE-") . time() . "." . $archivo->getClientOriginalExtension();
            // para evitar errores
            $newpath = str_replace("storage/", "", $path);
            $archivo->storeAs($newpath, $imagenName, 'public');
            $archivoComprimido = $imagenName;
        }
        $relativePath = "{$path}/{$archivoComprimido}";
        if ($model) {
            $model->update([
                $columnName => $relativePath,
            ]);
        }
        if ($oldPath && file_exists(public_path($oldPath))) {
            unlink(public_path($oldPath));
        }
        return $relativePath;
    }
}
