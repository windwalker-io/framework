<?php $__env->startSection('sidebar'); ?>
    @parent

    <p>This is appended to the master sidebar.</p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <p>This is my body content.</p>
<?php $__env->stopSection(); ?>

<?php echo $__env->render('layouts.master', $this->arrayExcept(get_defined_vars(), array('__data', '__hash'))); ?>