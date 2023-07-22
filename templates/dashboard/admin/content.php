<?php
$dashboard_permalink = apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'permalink-dashboard', 'dashboard' );
$dashboard_permalink = site_url( $dashboard_permalink );
?>
<?php do_action( 'futurewordpress/project/quizandfiltersearch/parts/call', 'before_homecontent' ); ?>
<div>
    <?php do_action( 'futurewordpress/project/quizandfiltersearch/parts/call', 'homecontent' ); ?>
</div>
<?php do_action( 'futurewordpress/project/quizandfiltersearch/parts/call', 'after_homecontent' ); ?>
