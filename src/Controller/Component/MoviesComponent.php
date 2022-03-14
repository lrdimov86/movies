<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Lib\ImageCacher;
use Cake\Utility\Inflector;
use Cake\Utility\Hash;

/**
 * Movies component
 */
class MoviesComponent extends Component
{
    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'url'=>null,
        'format'=>'json',
        'data'=>null
    ];

    /**
     * configuration.
     *
     * @var array
     */
    protected $_config = [];

    /**
     * JSON string with movies.
     *
     * @var array
     */
    public $stream = [];

    /**
     * Sets up the component by initialing instance variables.
     *
     * @param array $config
     */
    public function initialize(array $config):void
    {
        $this->_config = array_merge($this->_defaultConfig,$config);        
    }

    public function fetchStream(){
        if($this->_config['format'] == 'json'){
            return $this->fetchJsonStream();
        }
    }

    /**
     * Decodes the JSON string in $stream var and returns an array
     * 
     * @return array
     */
    public function fetchJsonStream(){
        $this->setStream();
        $json = $this->stream;

        if(!$this->isStringUtf8($json)){            
            $json = utf8_encode($json);
        }

        return json_decode($json,true);
    }

    /**
     * Return 1 if $string has utf-8 encoding, otherwise returns null 
     * 
     * @param string $string
     * @return null|1
     */
    public function isStringUtf8($string){
        return preg_match('//u', $string);
    }

    /**
     * Populates $stream var with either JSON string from initials configuration(takes precedence) or response from 
     * url in configuration
     * 
     * @return void
     */
    public function setStream(){
        if($this->_config['data']==null){
            $this->stream = file_get_contents($this->_config['url']);
        }else{            
            $this->stream = $this->_config['data'];
        }
    }

    /**
     * Parses movies array into a nicely formatted array and limits numbers of movies based on $page and $offset
     * 
     * @param int $page
     * @param int $offset
     * @return array
     */
    public function fetchList($page=1, $offset=10){
        $data = $this->fetchStream();
        
        $totalMovieCount = count($data);
        $movies = [];
        $start = ($page-1)*$offset;
        $limit = $start+$offset;
        $limit = $limit<=$totalMovieCount?$limit:$totalMovieCount;
        
        for($i=$start; $i<$limit; $i++){            
            $details = $this->buildMovieDetails($data[$i]);
            $keyArtImages = $this->fetchImages($data[$i]['keyArtImages'],'key-art-images',$details['id']);
            $details['images'] = $keyArtImages;

            $movies[] = $details;
        }

        return [
            'list' => $movies,
            'count' => $totalMovieCount,
            'pages' => ceil($totalMovieCount/$offset)
        ];
    }

    /**
     * Returns an array containing all details about a single movie specified by $id
     * 
     * @param string $id
     * @return void
     */
    public function fetchMovie($id){
        $data = $this->fetchStream();
                
        $movie = Hash::extract($data,"{n}[id={$id}]");
        $details = $this->buildMovieDetails($movie[0]);

        $cardImages = $this->fetchImages($movie[0]['cardImages'],'card-images',$details['id']);
        $details['cardImages'] = $cardImages;

        $keyArtImages = $this->fetchImages($movie[0]['keyArtImages'],'key-art-images',$details['id']);
        $details['images'] = $keyArtImages;

        return $details;
    }

    /**
     * Parses an entry from the movies array into a more easily digestible format
     * 
     * @param array $movie
     * @return array
     */
    public function buildMovieDetails($movie){
        $id = $movie['id'];
        $quote = isset($movie['quote'])?$movie['quote']:'';
        $reviewAuthor = isset($movie['reviewAuthor'])?$movie['reviewAuthor']:'';        
        $genres = isset($movie['genres'])?$movie['genres']:'';
                                                         
        $details = [
            'id' => $id,
            'title' => $movie['headline'],
            'quote' => $quote,
            'titleId' => Inflector::camelize($movie['headline']),
            'year' => $movie['year'],            
            'genres' => $genres,
            'cast' => implode(', ',Hash::extract($movie['cast'],'{n}.name')),                
            'directors' => implode(', ',Hash::extract($movie['directors'],'{n}.name')),
            'synopsis' => $movie['synopsis'],
            'rating' => $movie['cert'],
            'duration' => $movie['duration'],
            'durationHuman' => $this->humanizeDuration($movie['duration']),
            'durationSubtitle' => gmdate('g',$movie['duration']).'h '.gmdate('i',$movie['duration']).'m',
            'reviewAuthor' => $reviewAuthor,
            'review' => $movie['body'],
            'whereToWatch' => $this->whereToWatch($movie)
        ];

        return $details;
    }

    /**
     * Takes seconds in the form of a integer and returns string containing hours and minutes
     * 
     * @param int $seconds
     * @return string
     */
    public function humanizeDuration($seconds){
        $hours = gmdate('g',$seconds);
        $minutes = gmdate('i',$seconds);

        $humanizedDuration = $hours;
        $humanizedDuration .= $hours>1?' hours ':' hour ';
        $humanizedDuration .= $minutes;
        $humanizedDuration .= $minutes>1?' minutes':' minute';

        return $humanizedDuration;
    }

    /**
     * Checks if images referenced in a movie entry have been cached. If we find cached images we return url paths to 
     * the cached images. If we can not find cached images, we download them abd the return the cached image urls.
     * 
     * @param array $images
     * @param string $folder
     * @param string $id
     */
    public function fetchImages($images,$folder,$id){
        $moviePath = WWW_ROOT."img/{$id}";        
        $cachedFiles = [];

        $hashedFolderName = ImageCacher::hashFilePaths(Hash::extract($images,'{n}.url'));
        $folderPath = "{$moviePath}/{$folder}/{$hashedFolderName}";

        if (file_exists($folderPath)) {
            $files = array_diff(scandir($folderPath), array('.', '..'));

            foreach($files as $fileName){                
                $cachedFiles[] = "/webroot/img/{$id}/{$folder}/{$hashedFolderName}/{$fileName}";
            }

            return $cachedFiles;
        }

        return ImageCacher::cacheImages($images,$folder,$id);
    }

    /**
     * Parses viewingWindow array into a string
     * 
     * @param array $movie
     * @return string
     */
    public function whereToWatch($movie){
        if(!isset($movie['viewingWindow'])){
            return '';
        }

        $viewingWindow = $movie['viewingWindow'];

        $wayToWatch = isset($viewingWindow['wayToWatch'])?$viewingWindow['wayToWatch']:'';
        if($wayToWatch == ''){
            return '';
        }

        $watchString = "On {$viewingWindow['wayToWatch']}";

        $startDate = isset($viewingWindow['startDate'])?date('d F Y',strtotime($viewingWindow['startDate'])):'';
        if($startDate != ''){
            $watchString .= " starting {$startDate}";
        }

        $endDate = isset($viewingWindow['endDate'])?date('d F Y',strtotime($viewingWindow['endDate'])):'';
        if($endDate != ''){
            $watchString .= " until {$endDate}";
        }

        return $watchString;
    }
}
