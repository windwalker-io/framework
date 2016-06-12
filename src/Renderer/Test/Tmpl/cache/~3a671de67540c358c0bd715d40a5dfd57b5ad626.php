<html>
    <body>
        <?php $__env->startSection('sidebar'); ?>
            This is the master sidebar.
        <?php echo $__env->yieldSection(); ?>

        <div class="container">
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </body>
</html>
