<?php

class File extends CActiveRecord
{
	const DIR_PERMS = 0755;
	
	protected static $uploadPath;
	protected static $uploadPathUrl;
	protected static $validImageTypes = array('image/png', 'image/gif', 'image/jpg', 'image/jpeg', 'image/tiff');
	
	public static function getUploadPath()
	{
		if (null === self::$uploadPath) {
			self::setUploadPath(rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/uploads');
		}
		return self::$uploadPath;
	}
	
	public static function setUploadPath($path)
	{
		if (!is_dir($path) && !is_writable($path)) {
			throw new Exception("Invalid upload path '$path'. Check the directory exists and writable.");
		}
		self::$uploadPath = rtrim($path, '/');
	}
	
	public static function getUploadPathUrl()
	{
		if (null === self::$uploadPathUrl) {
			self::$uploadPathUrl = substr(self::getUploadPath(), strlen(rtrim($_SERVER['DOCUMENT_ROOT'], '/')));
		}
		return self::$uploadPathUrl;
	}
	
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function attributeLabels()
	{
		return array(
			'title' => Yii::t('file', 'Title'),
			'mime' => Yii::t('file', 'Mime type'),
			'extension' => Yii::t('file', 'Extension'),
			'size' => Yii::t('file', 'Size'),
			'width' => Yii::t('file', 'Width'),
			'height' => Yii::t('file', 'Height'),
			'category_id' => Yii::t('file', 'Category'),
			'create_time' => Yii::t('file', 'Create Time'),
		);
	}
	
	public function relations()
	{
		return array(
			'children' => array(self::HAS_MANY, 'File', 'parent_id'),
			'parent' => array(self::BELONGS_TO, 'File', 'parent_id'),
			'category' => array(self::BELONGS_TO, 'FileCategory', 'category_id'),
		);
	}
	
	public function rules()
	{
		return array(
			array('title', 'required', 'on' => 'create, update'),
			array('category_id', 'safe', 'on' => 'update'),
			array('title, mime, extension, category_id', 'safe', 'on' => 'search'),
		);
	}
	
	public function tableName()
	{
		return '{{file}}';
	}
	
	protected function afterDelete()
	{
		foreach ($this->children as $child) {
			$child->delete();
		}
		$fileName = self::getUploadPath() . '/' . $this->path;
		@unlink($fileName);
		parent::afterDelete();
	}
	
	protected function beforeSave()
	{
		if (parent::beforeSave()) {
			if ($this->isNewRecord) {
				$this->create_time = time();
				$this->user_id = Yii::app()->user->id;
			}
			$this->update_time = time();
			return true;
		}
		return false;
	}
	
	public function processUploading($name, $allowed_mime=null, $max_size=null)
	{
		$result = array();
		$instances = CUploadedFile::getInstancesByName($name);
		foreach ($instances as $instance) {
			if ($instance->hasError) {
				$result[$instance->name] = self::getErrorMessage($instance->error);
			} else {
				if ($max_size && $instance->size > $max_size) {
					$result[$instance->name] = Yii::t('file', 'File is too large');
				} elseif ($allowed_mime && !in_array($instance->type, (array) $allowed_mime)) {
					$result[$instance->name] = Yii::t('file', 'Unallowed mime type: {mime}', array(
						'{mime}' => $instance->type,
					));
				} elseif ($model = $this->createFromUploadedFile($instance)) {
					$result[$instance->name] = $model;
				} else {
					$result[$instance->name] = Yii::t('file', 'Error while copying a file');
				}
			}
		}
		return $result;
	}
	
	public function createFromUploadedFile(CUploadedFile $instance)
	{
		$class = get_class($this);
		$model = new $class();
		$model->extension = $instance->extensionName;
		$model->title = $instance->name;
		$model->size = $instance->size;
		$model->mime = $instance->type;
		if (!($model->path = $this->createPath($instance->name))) {
			return false;
		}
		if (strpos($instance->type, 'image/') === 0) {
			$info = getimagesize($instance->tempName);
			$model->width = $info[0];
			$model->height = $info[1];
		}
		$fileName = self::getUploadPath() . '/' . $model->path;
		if ($instance->saveAs($fileName)) {
			if ($model->save()) {
				return $model;
			}
			@unlink($fileName);
		}
		return false;
	}
	
	protected function createPath($name)
	{
		$sub = date('Y/m/d');
		$dir = self::getUploadPath() . '/' . $sub;
		if (! is_dir($dir)) {
			if (! mkdir($dir, self::DIR_PERMS, true)) {
				return false;
			}
		}
		$ext = explode('.', $name);
		$name = md5($name.time()) . '.' . end($ext);
		return $sub . '/' . $name;
	}
	
	public static function getErrorMessage($code)
	{
		switch ($code) {
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
					return Yii::t('file', 'File is too large');
					
				case UPLOAD_ERR_OK:
					return Yii::t('file', 'All is ok');
				
				case UPLOAD_ERR_PARTIAL:
					return Yii::t('file', 'The uploaded file was only partially uploaded');
					
				case UPLOAD_ERR_NO_TMP_DIR:
					return Yii::t('file', 'Missing a temporary folder');
					
				case UPLOAD_ERR_CANT_WRITE:
					return Yii::t('file', 'Failed to write file to disk');
		}
		return Yii::t('file', 'Unknow error');
	}
	
	public function getFriendlySize()
	{
		$bytes = $this->size;
		if ($bytes < 1024) return "$bytes B";
		if ($bytes < 1048576) return sprintf("%0.1f KB",  $bytes / 1024);
		return sprintf("%0.1f MB",  $bytes / 1048576);
	}
	
	public function getUrl($absolute=true)
	{
		return sprintf('http://%s%s/%s', $_SERVER['HTTP_HOST'], self::getUploadPathUrl(), $this->path);
	}
	
	public function getIsImage()
	{
		return in_array($this->mime, self::$validImageTypes);
	}
	
	public function getValidImageTypes()
	{
		return self::$validImageTypes;
	}
	
	public function getUrlResized($width, $height=0)
	{
		if (! $this->getIsImage()) {
			throw new Exception('Trying resize non-image file');
		}
		
		if (($width == 0 || $this->width == $width) && ($height == 0 || $this->height == $height)) {
			return $this->getUrl();
		}
		
		$criteria = new CDbCriteria();
		$criteria->compare('parent_id', $this->id);
		if ($width) {
			$criteria->compare('width', $width);
		}
		if ($height) {
			$criteria->compare('height', $height);
		}
		$criteria->limit = 1;
		
		$resized = $this->find($criteria);
		if (! $resized) {
			$resized = $this->makeResized($width, $height);
		}
		
		return $resized->getUrl();
	}
	
	public function getImageSizeReal()
	{
		return getimagesize(self::getUploadPath() . '/' . $this->path);
	}
	
	public function makeResized($width, $height)
	{
		if (! $this->getIsImage()) {
			throw new Exception('Trying resize non-image file');
		}
		
		$file = self::getUploadPath() . '/' . $this->path;
		
		$resizedPath = sprintf('%s/%d_%d_%s', trim(dirname($this->path), '/'), $width, $height, basename($this->path));
		$resizedFile = self::getUploadPath() . '/' . $resizedPath;
		
		$dir = dirname($resizedFile);
		if (! is_writable($dir)) {
			throw new Exception("Directory $dir is not writable");
		}
		
		Yii::import('application.extensions.image.Image');
		
		$image = new Image($file);
		if ($width > 0 && $height > 0) {
			$ratio = $this->width / $this->height;
			$resultRatio = $width / $height;
			if ($resultRatio > $ratio) {
				$image->crop($this->width, intval($this->width / $resultRatio));
			} elseif ($resultRatio < $ratio) {
				$image->crop(intval($resultRatio * $this->height), $this->height);
			}
		}
		$image->resize($width, $height);
		$image->save($resizedFile);
		$info = getimagesize($resizedFile);
		
		$class = get_class($this);
		$model = new $class();
		$model->extension = $this->extension;
		$model->title = $this->title;
		$model->size = filesize($resizedFile);
		$model->mime = $info['mime'];
		$model->path = $resizedPath;
		$model->width = $width;
		$model->height = $height;
		$model->parent_id = $this->id;
		if (! $model->save()) {
			@unlink($resizedFile);
			return false;
		}
		return $model;
	}
	
	public function resize($width, $height)
	{
		if (! $this->getIsImage()) {
			throw new Exception('Trying resize non-image file');
		}
		
		Yii::import('application.extensions.image.Image');
		
		$file = self::getUploadPath() . '/' . $this->path;
		$image = new Image($file);
		$image->resize($width, $height);
		$image->save();
		foreach ($this->children as $child) {
			$child->delete();
		}
		$this->updateImageProps();
	}
	
	public function crop($width, $height, $top='center', $left='center')
	{
		if (! $this->getIsImage()) {
			throw new Exception('Trying crop non-image file');
		}
		
		Yii::import('application.extensions.image.Image');
		
		$file = self::getUploadPath() . '/' . $this->path;
		$image = new Image($file);
		$image->crop($width, $height, $top, $left);
		$image->save();
		foreach ($this->children as $child) {
			$child->delete();
		}
		$this->updateImageProps();
	}
	
	protected function updateImageProps()
	{
		clearstatcache();
		$file = self::getUploadPath() . '/' . $this->path;
		$info = getimagesize($file);
		$this->width = $info[0];
		$this->height = $info[1];
		$this->size = filesize($file);
		$this->updateByPk($this->id, array(
			'width' => $this->width,
			'height' => $this->height,
			'size' => $this->size,
		));
	}
	
	public function search($params=array())
	{
		$criteria = new CDbCriteria($params);
		$criteria->compare('t.title', $this->title, true);
		$criteria->compare('t.category_id', $this->category_id);
		$criteria->compare('parent_id', 0);
		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}
	
	public function __toString()
	{
		return CHtml::link($this->title, $this->getUrl(), array('target' => '_blank'));
	}
}
