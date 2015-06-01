<html>
<head>
    <title><?=$this->e($title)?></title>
</head>
<body>

<img src="logo.png">

<div id="page">
    <?=$this->section('page')?>
</div>

<div id="sidebar">
    <?php if ($this->section('sidebar')): ?>
    <?=$this->section('sidebar')?>
    <?php else: ?>
    <?=$this->fetch('default-sidebar')?>
    <?php endif ?>
</div>

</body>
</html>
