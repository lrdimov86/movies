<?php
namespace Lib;

use Cake\Utility\Hash;

/**
 * Used to aide in the caching movie images
 */
class ImageCacher
{
    /**
     * Downloads and stores images from url in a movie entry. We concatinate all the image urls into string and hash
     * that string to create the folder the holds the images. This way if a single image url in a movie changes, the 
     * hash will not match and the new image(s) will be cached
     * 
     * @param array $images
     * @param string $folder
     * @param string $id
     * @return array
     */
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

    /**
     * Concatinates entries in an array into a comma separated string and md5 hashes the string
     * 
     * @param array $filePaths
     * @return string
     */
    public static function hashFilePaths($filePaths)
    {
        return md5(implode(',',$filePaths));
    }

    /**
     * Returns path on the server where the images should be stored
     *      
     * @param string $id
     * @param string $folder     
     * @param string $hashedFolderName
     * @return string
     */
    public static function buildFolderPath($id,$folder,$hashedFolderName)
    {
        $moviePath = WWW_ROOT."img/{$id}";
        $folderPath = "{$moviePath}/{$folder}/{$hashedFolderName}";

        return $folderPath;
    }

    /**
     * Returns url to cached image file
     *      
     * @param string $id
     * @param string $folder     
     * @param string $hashedFolderName
     * @param string $fileName
     * @return string
     */
    public static function buildCachedImageUrl($id,$folder,$hashedFolderName,$fileName)
    {
        return "/webroot/img/{$id}/{$folder}/{$hashedFolderName}/{$fileName}";
    }
}
