<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Movies Controller
 * 
 */
class MoviesController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Movies', ['url'=>'https://mgtechtest.blob.core.windows.net/files/showcase.json']);
    }

    /**
     * Retrieves all movies from JSON string 10 at a time
     *
     * @return void
     */
    public function index()
    {
        $page = $this->request->getQuery('page')?$this->request->getQuery('page'):1;

        $movies = $this->Movies->fetchList($page);        
        $title_for_layout = 'Movies';

        $this->set(compact('movies', 'page','title_for_layout'));
    }

    /**
     * Displays a detailed view of a movie
     *
     * @param string $id
     * @return void     
     */
    public function details($id)
    {
        $movie = $this->Movies->fetchMovie($id);        

        $title_for_layout = $movie['title'];
        $pageSubtitle = $movie['year'].' | '.$movie['rating'].' | '.$movie['durationSubtitle'];
        $backBtn = true;

        $this->set(compact('movie','title_for_layout','pageSubtitle','backBtn'));
    }
}
