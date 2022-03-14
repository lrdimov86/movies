<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Movies Controller
 *
 * @method \App\Model\Entity\Movie[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class MoviesController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Movies', ['url'=>'https://mgtechtest.blob.core.windows.net/files/showcase.json']);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $page = $this->request->getQuery('page')?$this->request->getQuery('page'):1;

        $movies = $this->Movies->fetchList($page);        
        $title_for_layout = 'Movies';

        $this->set(compact('movies', 'page','title_for_layout'));
    }

    /**
     * Details method
     *
     * @param string|null $id Movie id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
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
