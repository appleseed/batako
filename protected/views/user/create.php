<?php
/* @var $this UserController */
/* @var $model user */

$this->breadcrumbs=array(
	'Users'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List user', 'url'=>array('index')),
	array('label'=>'Manage user', 'url'=>array('admin')),
);

?>
<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>