<?php foreach ($posts as $post): ?>
    <div class="post">
        <h3><?= esc($post['title']) ?></h3>
        <p><?= esc($post['category']) ?></p>
        <p><?= esc($post['body']) ?></p>
        <img src="<?= esc($post['image']) ?>" alt="Ieraksta attēls" class="img-fluid">
    </div>
<?php endforeach; ?>
