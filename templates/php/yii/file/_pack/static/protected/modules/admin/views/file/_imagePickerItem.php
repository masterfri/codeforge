<a href="#" title="<?php echo CHtml::encode($data->title); ?> (<?php echo $data->width; ?>x<?php echo $data->height; ?>)" rel="<?php echo $data->id; ?>" class="thumbnail" data-toggle="tooltip">
	<img src="<?php echo $data->getUrlResized($w, $h); ?>" alt="" width="<?php echo $w > 0 ? $w : 'auto'; ?>" height="<?php echo $h > 0 ? $h : 'auto'; ?>" />
</a>
