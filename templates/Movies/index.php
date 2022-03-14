<?php foreach($movies['list'] as $movie): ?>

<div class="card mb-3">
    <div class="row g-0">
        <div class="col-md-1">
        
            <div id="<?= $movie['titleId'] ?>" class="carousel slide" data-bs-interval="false" data-bs-ride="carousel">
                <div class="carousel-inner">

                    <?php foreach($movie['images'] as $imageUrl): ?>
                    <div class="carousel-item active">
                        <img src="<?= $imageUrl ?>" class="d-block w-100">
                    </div>
                    <?php endforeach; ?>

                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#<?= $movie['titleId'] ?>" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#<?= $movie['titleId'] ?>" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>


        </div>
        <div class="col-md-11">
            <div class="card-body">
                <h5 class="card-title">
                    <a class="movie-link" href="/movies/details/<?= $movie['id'] ?>">
                        <?= $movie['title']." - ".$movie['year'] ?>
                    </a>
                    <?php foreach($movie['genres'] as $genre): ?>
                        <span class="badge rounded-pill bg-light text-dark"><?= $genre ?></span>
                    <?php endforeach; ?>
                </h5>
                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <th class="squeeze" >Directors</th>
                            <td><?= $movie['directors'] ?></td>
                        </tr>
                        <tr>
                            <th class="squeeze" >Cast</th>
                            <td><?= $movie['cast'] ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>    
</div>
<?php endforeach; ?>

<div class="row g-0">
    <div class="col-md-12">
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item">
                    <a class="page-link" href="/movies?page=1" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                
                <?php for($i=1; $i<=$movies['pages']; $i++): ?>

                    <li class="page-item <?= $page==$i?'active':'' ?>">
                        <a class="page-link" href="/movies?page=<?= $i ?>"><?= $i; ?></a>
                    </li>

                <?php endfor; ?>
                
                <li class="page-item">
                    <a class="page-link"  href="/movies?page=<?= $movies['pages'] ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>

    </div>
</div>