<?php
declare(strict_types=1);

namespace App\Test\TestCase\Lib;

use Cake\TestSuite\TestCase;
use Cake\Utility\Inflector;
use Cake\Utility\Hash;
use Lib\ImageCacher;

/**
 * Lib\ImageCacher Test Case
 */
class ImageCacherTest extends TestCase
{

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }


    /**
     * Test hashFilePaths method
     *
     * @return void
     * @uses \Lib\ImageCacher::hashFilePaths()
     */
    public function testHashFilePaths(): void
    {   
        $urls = [
            'https://www.thefirsturl.com',
            'https://www.thesecondurl.com'
        ];
        
        $hashFilePaths = ImageCacher::hashFilePaths($urls);
        $this->assertEquals(preg_match('/^[a-f0-9]{32}$/', $hashFilePaths),1);
    }

    /**
     * Test buildFolderPath method
     *
     * @return void
     * @uses \Lib\ImageCacher::buildFolderPath()
     */
    public function testBuildFolderPath(): void
    { 
        $urls = [
            'https://www.thefirsturl.com',
            'https://www.thesecondurl.com'
        ];
        $id = '8ad589013b496d9f013b4c0b684a4a5d';
        $folder = 'card-images';
        $hashedFolderName = ImageCacher::hashFilePaths($urls);

        $folderPath = ImageCacher::buildFolderPath($id,$folder,$hashedFolderName);
        $expected = "/var/www/mindgeek/webroot/img/{$id}/{$folder}/{$hashedFolderName}";
        
        $this->assertEquals($expected, $folderPath);
    }



    /**
     * Test buildCachedImageUrl method
     *
     * @return void
     * @uses \Lib\ImageCacher::buildCachedImageUrl()
     */
    public function testBuildCachedImageUrl(): void
    { 
        $urls = [
            'https://www.thefirsturl.com',
            'https://www.thesecondurl.com'
        ];

        $id = '8ad589013b496d9f013b4c0b684a4a5d';
        $folder = 'card-images';
        $hashedFolderName = ImageCacher::hashFilePaths($urls);
        $fileName = 'some-file-name.jpg';

        $url = ImageCacher::buildCachedImageUrl($id,$folder,$hashedFolderName,$fileName);        
        $expected = "/webroot/img/{$id}/{$folder}/{$hashedFolderName}/{$fileName}";
        
        $this->assertEquals($expected, $url);
    }
    
}