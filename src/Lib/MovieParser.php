<?php
namespace Lib;

use Cake\Utility\Hash;

/**
 * Used to aide in the parsing movie list json string
 */
class MovieParser
{
    public static function cacheImages($images,$folder,$id){
        $moviePath = WWW_ROOT."img/{$id}";
        $hashedFolderName = MovieParser::hashFilePaths(Hash::extract($images,'{n}.url'));

        $folderPath = "{$moviePath}/{$folder}/{$hashedFolderName}";
        $cachedFiles = [];
        
        mkdir($folderPath, 0774, true);        

        foreach($images as $image){
            $fileName = basename($image['url']);
            $filePath = "{$folderPath}/{$fileName}";
            $src = null;
            
            if(file_exists($filePath)){
                $src = "/webroot/img/{$id}/{$folder}/{$hashedFolderName}/{$fileName}";
            }else{
                
                $imageBlob = null;                
                $imageBlob = @file_get_contents($image['url']);

                if($imageBlob != null){                    
                    file_put_contents($filePath, $imageBlob);
                    $src = "/webroot/img/{$id}/{$folder}/{$hashedFolderName}/{$fileName}";
                }
            }

            if($src != null){
                $cachedFiles[] = $src;
            }
        }

        return $cachedFiles;
    }

    public static function hashFilePaths($filePaths){
        return md5(implode(',',$filePaths));
    }
}
