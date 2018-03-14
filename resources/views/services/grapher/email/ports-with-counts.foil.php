<html>
<head>
    <title>Ports with Issues Report :: <?= $t->categoryDesc ?></title>
</head>

<body>

<h1>Ports with Issues Report :: <?= $t->categoryDesc ?></h1>

<br><br>

<?php foreach( $t->ports as $p ): ?>

    <h2><?= $p['cust']['name'] ?></h2>

    <ul>
        <li>
            <strong>INBOUND:</strong> packets inbound yesterday: <?= $p['in'] ?>.
        </li>
        <li>
            <strong>OUTBOUND:</strong> packets outbound yesterday: <?= $p['out'] ?>.
        </li>
    </ul>

    <p>
        <a href="<?= route( "customer@overview" , [ "id" => $p['cust']['id'] ] )  ?>">
            <img src="data:image/png;base64,<?= base64_encode( $p['png'] ) ?>">
        </a>
    </p>

    <br><hr><br>

<?php endforeach; ?>


<?php if( count( $t->ports ) == 0 ): ?>
    <p>
        No ports were found with errors.
    </p>
<?php endif; ?>

<br><br>

<p>
    <em>
        This report was generated by <a href="<?= url('') ?>">IXP Manager</a> for <?= env( 'IDENTITY_ORGNAME' ) ?>.
    </em>
</p>

</body>
</html>
