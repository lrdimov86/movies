<?php
namespace Lib;

use Cake\Utility\Hash;

/**
 * Used to aide in the caching movie images
 */
class ImageCacher
{
    public static function cacheImages($images,$folder,$id)
    {        
        $hashedFolderName = ImageCacher::hashFilePaths(Hash::extract($images,'{n}.url'));        
        $folderPath = ImageCacher::buildFolderPath($id,$folder,$hashedFolderName);

        $cachedFiles = [];
        
        mkdir($folderPath, 0774, true);        

        foreach($images as $image){
            $fileName = basename($image['url']);
            $filePath = "{$folderPath}/{$fileName}";
            $src = null;
            
            if(file_exists($filePath)){
                $src = ImageCacher::buildCachedImageUrl($id,$folder,$hashedFolderName,$fileName);
            }else{
                
                $imageBlob = null;                
                $imageBlob = @file_get_contents($image['url']);

                if($imageBlob != null){                    
                    file_put_contents($filePath, $imageBlob);
                    $src = ImageCacher::buildCachedImageUrl($id,$folder,$hashedFolderName,$fileName);
                }
            }

            if($src != null){
                $cachedFiles[] = $src;
            }
        }

        return $cachedFiles;
    }

    public static function hashFilePaths($filePaths)
    {
        return md5(implode(',',$filePaths));
    }

    public static function buildFolderPath($id,$folder,$hashedFolderName)
    {
        $moviePath = WWW_ROOT."img/{$id}";
        $folderPath = "{$moviePath}/{$folder}/{$hashedFolderName}";

        return $folderPath;
    }

    public static function buildCachedImageUrl($id,$folder,$hashedFolderName,$fileName)
    {
        return "/webroot/img/{$id}/{$folder}/{$hashedFolderName}/{$fileName}";
    }
}
