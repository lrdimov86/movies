<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Lib\MovieParser;
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
        'format'=>'json'
    ];

    /**
     * configuration.
     *
     * @var array
     */
    protected $_config = [];

    /**
     * JSON array with movies.
     *
     * @var array
     */
    public $stream = [];

    /**
     * Sets up the component by initialing instance variables.
     *
     * @param Controller $controller
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

    public function fetchJsonStream(){
        $json = file_get_contents($this->_config['url']);

        if(!$this->isStringUtf8($json)){            
            $json = utf8_encode($json);
        }

        $stream = json_decode($json,true);        

        return $stream;
    }

    public function isStringUtf8($string){
        return preg_match('//u', $string);
    }

    public function setStream(){
        $this->stream = $this->fetchStream();
    }

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

    public function humanizeDuration($seconds){
        $hours = gmdate('g',$seconds);
        $minutes = gmdate('i',$seconds);

        $humanizedDuration = $hours;
        $humanizedDuration .= $hours>1?' hours ':' hour ';
        $humanizedDuration .= $minutes;
        $humanizedDuration .= $minutes>1?' minutes':' minute';

        return $humanizedDuration;
    }

    public function fetchImages($images,$folder,$id){
        $moviePath = WWW_ROOT."img/{$id}";        
        $cachedFiles = [];

        $hashedFolderName = MovieParser::hashFilePaths(Hash::extract($images,'{n}.url'));
        $folderPath = "{$moviePath}/{$folder}/{$hashedFolderName}";

        if (file_exists($folderPath)) {
            $files = array_diff(scandir($folderPath), array('.', '..'));

            foreach($files as $fileName){                
                $cachedFiles[] = "/webroot/img/{$id}/{$folder}/{$hashedFolderName}/{$fileName}";
            }

            return $cachedFiles;
        }

        return MovieParser::cacheImages($images,$folder,$id);
    }

    public function whereToWatch($movie){
        if(!isset($movie['viewingWindow'])){
            return '';
        }

        $wayToWatch = isset($viewingWindow['wayToWatch'])?$viewingWindow['wayToWatch']:'';
        if($wayToWatch == ''){
            return '';
        }

        $watchString = "On {$movie['wayToWatch']}";

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
