<?php if ($pager->haveToPaginate()) : ?>
<?php echo link_to('< 前', 'community/list?page=' . $pager->getPreviousPage()) ?>&nbsp;
<?php echo link_to('次 >', 'community/list?page=' . $pager->getNextPage()) ?>
<?php endif; ?>

<ul>
<?php foreach ($pager->getResults() as $community) : ?>
<li><?php echo link_to($community->getName(), 'community/home?id=' . $community->getId()); ?></li>
<?php endforeach; ?>
</ul>
