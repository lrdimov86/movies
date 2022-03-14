<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Component;

use App\Controller\Component\MoviesComponent;
use Cake\Controller\ComponentRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Inflector;
use Cake\Utility\Hash;

/**
 * App\Controller\Component\MoviesComponent Test Case
 */
class MoviesComponentTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Controller\Component\MoviesComponent
     */
    protected $MoviesComponent;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $registry = new ComponentRegistry();
        $config = [
            'url'=>'https://some.test.url',
            'data'=>'[{"body":"The body of a review","synopsis":"this is the synopsis","cardImages":[{"url":"https:\/\/some.nonexistant.url"},{"url":"https:\/\/some.nonexistant.url"}],"cast":[{"name":"Billy Crystal"}],"cert":"U","directors":[{"name":"Andy Fickman"}],"duration":5940,"genres":["comedy","family"],"headline":"Parental Guidance","id":"8ad589013b496d9f013b4c0b684a4a5d","keyArtImages":[{"url":"https:\/\/some.nonexistant.url"},{"url":"https:\/\/some.nonexistant.url"}],"lastUpdated":"2013-07-15","quote":"an intriguing pairing of Bette Midler and Billy Crystal","rating":3,"reviewAuthor":"Tim Evans","viewingWindow":{"startDate":"2013-12-27","wayToWatch":"Sky Movies","endDate":"2015-01-21"},"year":2012}]'
        ];
        $this->MoviesComponent = new MoviesComponent($registry,$config);
        $this->mockStream = '';
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->MoviesComponent);

        parent::tearDown();
    }

    /**
     * Test fetchStream method
     *
     * @return void
     * @uses \App\Controller\Component\MoviesComponent::fetchStream()
     */
    public function testFetchStream(): void
    {   
        $this->MoviesComponent->setStream();        
        $data = $this->MoviesComponent->fetchStream();

        $this->assertArrayHasKey(0,$data);
        $this->assertEquals($data[0]['body'],"The body of a review");
        $this->assertEquals($data[0]['headline'],"Parental Guidance");
    }

    /**
     * Test fetchJsonStream method
     *
     * @return void
     * @uses \App\Controller\Component\MoviesComponent::fetchJsonStream()
     */
    public function testFetchJsonStream(): void
    {
        $this->MoviesComponent->setStream();
        $data = $this->MoviesComponent->fetchStream();

        $this->assertArrayHasKey(0,$data);
        $this->assertEquals($data[0]['body'],"The body of a review");
        $this->assertEquals($data[0]['headline'],"Parental Guidance");
    }

    /**
     * Test isStringUtf8 method returns false
     *
     * @return void
     * @uses \App\Controller\Component\MoviesComponent::isStringUtf8()
     */
    public function testIsStringUtf8ReturnsFalse(): void
    {
        $this->assertFalse($this->MoviesComponent->isStringUtf8("\xc3\x28"));
    }

    /**
     * Test isStringUtf8 method returns true(1)
     *
     * @return void
     * @uses \App\Controller\Component\MoviesComponent::isStringUtf8()
     */
    public function testIsStringUtf8ReturnsTrue(): void
    {        
        $this->assertEquals($this->MoviesComponent->isStringUtf8("test"),1);
    }

    /**
     * Test setStream method
     *
     * @return void
     * @uses \App\Controller\Component\MoviesComponent::setStream()
     */
    public function testSetStream(): void
    {
        $this->MoviesComponent->setStream();
        $this->assertNotEmpty($this->MoviesComponent->stream);
    }

    /**
     * Test fetchList method
     *
     * @return void
     * @uses \App\Controller\Component\MoviesComponent::fetchList()
     */
    public function testFetchList(): void
    {
        $data = $this->MoviesComponent->fetchList();
        
        $this->assertArrayHasKey('list',$data);
        $this->assertEquals($data['list'][0]['id'],"8ad589013b496d9f013b4c0b684a4a5d");
        $this->assertEquals($data['pages'],1);
        $this->assertEquals($data['count'],1);
    }

    /**
     * Test fetchMovie method
     *
     * @return void
     * @uses \App\Controller\Component\MoviesComponent::fetchMovie()
     */
    public function testFetchMovie(): void
    {
        $data = $this->MoviesComponent->fetchMovie("8ad589013b496d9f013b4c0b684a4a5d");

        $this->assertEquals($data['id'],"8ad589013b496d9f013b4c0b684a4a5d");
        $this->assertEmpty($data['cardImages']);
        $this->assertEmpty($data['images']);
    }

    /**
     * Test buildMovieDetails method
     *
     * @return void
     * @uses \App\Controller\Component\MoviesComponent::buildMovieDetails()
     */
    public function testBuildMovieDetails(): void
    {        
        $rawList = $this->MoviesComponent->fetchStream();
        $data = $this->MoviesComponent->buildMovieDetails($rawList[0]);

        $movie = $rawList[0];
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
            'durationHuman' => $this->MoviesComponent->humanizeDuration($movie['duration']),
            'durationSubtitle' => gmdate('g',$movie['duration']).'h '.gmdate('i',$movie['duration']).'m',
            'reviewAuthor' => $reviewAuthor,
            'review' => $movie['body'],
            'whereToWatch' => $this->MoviesComponent->whereToWatch($movie)
        ];

        $this->assertEquals(json_encode($data),json_encode($details));
    }

    /**
     * Test humanizeDuration method
     *
     * @return void
     * @uses \App\Controller\Component\MoviesComponent::humanizeDuration()
     */
    public function testHumanizeDuration(): void
    {        
        $this->assertEquals('1 hour 39 minutes',$this->MoviesComponent->humanizeDuration(5960));
        $this->assertEquals('2 hours 01 minute',$this->MoviesComponent->humanizeDuration(7260));
        
    }

    /**
     * Test fetchImages method return cached images
     *
     * @return void
     * @uses \App\Controller\Component\MoviesComponent::fetchImages()
     */
    public function testFetchImagesReturnsCachedImages(): void
    {
        $rawList = $this->MoviesComponent->fetchStream();
        
        $cardImages = $this->MoviesComponent->fetchImages($rawList[0]['cardImages'],'card-images','test');
        $keyArtImages = $this->MoviesComponent->fetchImages($rawList[0]['cardImages'],'key-art-images','test');

        $cardImageUrl = '/webroot/img/test/card-images/d0ad3c57d25256f428b75299de5f3a12/LPA-Parental-guidance-LPA-to-LP4.jpg';
        $keyArtImageUrl = '/webroot/img/test/key-art-images/d0ad3c57d25256f428b75299de5f3a12/Parental-Guidance-KA-KA-to-KP3.jpg';

        $this->assertEquals($cardImageUrl,$cardImages[0]);
        $this->assertEquals($keyArtImageUrl,$keyArtImages[0]);        
    }

    /**
     * Test fetchImages method return empty
     *
     * @return void
     * @uses \App\Controller\Component\MoviesComponent::fetchImages()
     */
    public function testFetchImagesReturnsEmpty(): void
    {
        $rawList = $this->MoviesComponent->fetchStream();
        
        $cardImages = $this->MoviesComponent->fetchImages($rawList[0]['cardImages'],'card-images',$rawList[0]['id']);
        $keyArtImages = $this->MoviesComponent->fetchImages($rawList[0]['cardImages'],'key-art-images',$rawList[0]['id']);
        
        $this->assertEmpty($cardImages);
        $this->assertEmpty($keyArtImages);
    }

    /**
     * Test whereToWatch method
     *
     * @return void
     * @uses \App\Controller\Component\MoviesComponent::whereToWatch()
     */
    public function testWhereToWatch(): void
    {
        $movie = [];
        $whereToWatch = $this->MoviesComponent->whereToWatch($movie);
        $this->assertEquals('',$whereToWatch);

        $movie = ['viewingWindow' => []];
        $whereToWatch = $this->MoviesComponent->whereToWatch($movie);
        $this->assertEquals('',$whereToWatch);

        $movie = ['viewingWindow' => [
            'wayToWatch' => 'Sky Movies',
            'startDate' => '2013-01-21',
            'endDate' => '2015-01-21'
        ]];
        
        $whereToWatch = $this->MoviesComponent->whereToWatch($movie);
        $this->assertEquals('On Sky Movies starting 21 January 2013 until 21 January 2015',$whereToWatch);
    }
}
