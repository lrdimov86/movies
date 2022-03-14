<div class="card mb-3">
    <div class="row g-0">
        <div class="col-lg-1 col-md-2 col-sm-12">
            <img src="<?= $movie['images'][0] ?>" class="img-fluid" alt="...">
        </div>
        <div class="col-lg-11 col-md-10 col-sm-12">
            <div class="card-body">
                <p class="card-text">
                <?php foreach($movie['genres'] as $genre): ?>
                    <span class="badge rounded-pill bg-light text-dark"><?= $genre ?></span>
                <?php endforeach; ?>
                </p>

                <p class="card-text"><?= $movie['synopsis'] ?>
            </div>
        </div>
    </div>

    <div class="row g-0">
        <div class="col-md-12">
            <table class="table table-sm table-card">
                <tbody>
                    <tr>
                        <th class="squeeze" >Directors</th>
                        <td><?= $movie['directors'] ?></td>
                    </tr>
                    <tr>
                        <th class="squeeze" >Cast</th>
                        <td><?= $movie['cast'] ?></td>
                    </tr>
                    <tr>
                        <th class="squeeze" >Rating</th>
                        <td><?= $movie['rating'] ?></td>
                    </tr>
                    <tr>
                        <th class="squeeze" >Duration</th>
                        <td><?= $movie['durationHuman'] ?></td>
                    </tr>
                    <?php if($movie['whereToWatch'] != ''): ?>
                    <tr>
                        <th class="squeeze" >Where to watch</th>
                        <td>On <?= $movie['whereToWatch'] ?></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-body">
                <h3 class="card-title">Images</h3>

                <div class="cover-container">
                    <?php foreach($movie['cardImages'] as $cardImage): ?>
                        <div onclick="window.open('<?= $cardImage ?>', '_blank').focus();" 
                             class="cover-item" style="background-image: url('<?= $cardImage ?>')"></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if(!empty($movie['review'])): ?>
<div class="row">
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-body">
                <h3 class="card-title">Review</h3>
                <h6 class="card-subtitle mb-2 text-muted"><?= $movie['reviewAuthor'] ?></h6>

                <p class="card-text"><?= $movie['review'] ?></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>