<?php

$this->registerHelper('attribute_type', function ($invoker, $attribute) 
{
	switch ($attribute->getType()) {
		case Codeforge\Attribute::TYPE_INT: 
			return 'INTEGER' . ($attribute->getIsUnsigned() ? ' UNSIGNED' : '');
			
		case Codeforge\Attribute::TYPE_DECIMAL: 
			return 'DECIMAL' . (is_array($attribute->getSize()) ? sprintf('(%s)', implode(',', $attribute->getSize())) : '') . ($attribute->getIsUnsigned() ? ' UNSIGNED' : '');
			
		case Codeforge\Attribute::TYPE_CHAR:
			return sprintf('VARCHAR(%d)', $attribute->getSize() ? $attribute->getSize() : 250);
			
		case Codeforge\Attribute::TYPE_TEXT: 
			return 'TEXT';
			
		case Codeforge\Attribute::TYPE_BOOL: 
			return 'TINYINT UNSIGNED';
			
		case Codeforge\Attribute::TYPE_INTOPTION: 
			return 'INTEGER';
			
		case Codeforge\Attribute::TYPE_STROPTION: 
			return sprintf('VARCHAR(%d)', $invoker->refer('optimal_option_len', $attribute->getOptions(), 10));
			
		case Codeforge\Attribute::TYPE_CUSTOM: 
		default:
			return 'TEXT';
	}
});

$this->registerHelper('model_columns', function ($invoker, $model) 
{
	$result = array();
	$columns = array();
	$keys = array();
	foreach ($model->getAttributes() as $attribute) {
		if (!$attribute->getIsCollection()) {
			if ($attribute->getType() == Codeforge\Attribute::TYPE_CUSTOM) {
				if ('many-to-one' == $invoker->refer('attribute_relation', $attribute)) {
					$definition = sprintf('`%s_id` INTEGER UNSIGNED', $attribute->getName());
					if ($attribute->getBoolHint('required')) {
						$definition .= ' NOT NULL';
					} else {
						$definition .= ' DEFAULT NULL';
					}
					$comment = sprintf('%s, many-to-one relation, foreign key refers to %s', $invoker->refer('attribute_label', $attribute), $attribute->getCustomType());
					$definition .= sprintf(' COMMENT %s', $invoker->refer('escape_value', $comment));
					$columns[] = $definition;
					$keys[] = sprintf('KEY `idx_%s_id` (`%s_id`)', $attribute->getName(), $attribute->getName());
				}
			} else {
				$definition = sprintf('`%s` %s', $attribute->getName(), $invoker->refer('attribute_type', $attribute));
				if ($attribute->getBoolHint('required')) {
					$definition .= ' NOT NULL';
				}
				$default = $attribute->getDefaultValue();
				if (null === $default) {
					if (!$attribute->getBoolHint('required')) {
						$definition .= ' DEFAULT NULL';
					}
				} elseif ($default instanceof Behavior) {
					$initializer = $invoker->getBuilder()->invokeHelper($default->getName(), false, true);
					$initial = $initializer->call($attribute, $default);
					if (null !== $initial) {
						$definition .= sprintf(' DEFAULT %s', $initial);
					}
				} else {
					$definition .= sprintf(' DEFAULT %s', $invoker->refer('escape_value', $default));
				}
				$comment = $invoker->refer('attribute_label', $attribute);
				$definition .= sprintf(' COMMENT %s', $invoker->refer('escape_value', $comment));
				$columns[] = $definition;
				if ($attribute->getBoolHint('searchable') || null !== $attribute->getHint('index')) {
					$index_length = (int) $attribute->getHint('index');
					if ($index_length > 0) {
						$keys[] = sprintf('KEY `idx_%s` (`%s`(%d))', $attribute->getName(), $attribute->getName(), $index_length);
					} else {
						$keys[] = sprintf('KEY `idx_%s` (`%s`)', $attribute->getName(), $attribute->getName());
					}
				}
			}
		}
	}
	foreach ($model->getReferences() as $attribute) {
		$relation = $invoker->refer('attribute_relation', $attribute);
		if ('one-to-many' == $relation || 'one-to-one' == $relation) {
			$backreference = $invoker->refer('attribute_back_reference', $attribute);
			if (!$backreference) {
				$column_name = sprintf('%s_id', $invoker->refer('table_name', $attribute->getOwner()));
				$definition = sprintf('`%s` INTEGER UNSIGNED', $column_name);
				if ('one-to-one' == $relation) {
					$definition .= ' NOT NULL';
					$comment = sprintf('one-to-one relation, foreign key refers to %s', $attribute->getOwner()->getName());
				} else {
					$definition .= ' DEFAULT NULL';
					$comment = sprintf('many-to-one relation, foreign key refers to %s', $attribute->getOwner()->getName());
				}
				$definition .= sprintf(' COMMENT %s', $invoker->refer('escape_value', $comment));
				$columns[] = $definition;
				$keys[] = sprintf('KEY `idx_%s` (`%s`)', $column_name, $column_name);
			}
		}
	}
	foreach ($columns as $column) {
		$result[] = $column;
	}
	foreach ($keys as $key) {
		$result[] = $key;
	}
	return $result;
});

$this->registerHelper('model_foreign_keys', function ($invoker, $model) 
{
	$result = array();
	foreach ($model->getAttributes() as $attribute) {
		if (!$attribute->getIsCollection()) {
			if ($attribute->getType() == Codeforge\Attribute::TYPE_CUSTOM) {
				if ('many-to-one' == $invoker->refer('attribute_relation', $attribute)) {
					$table = $invoker->refer('table_name', $model);
					$reftable = $invoker->refer('table_name', $attribute->getCustomType());
					$result[] = sprintf('ALTER TABLE `%s` ADD CONSTRAINT `fk_%s_id` FOREIGN KEY (`%s_id`) REFERENCES `%s` (`id`)', $table, $attribute->getName(), $attribute->getName(), $reftable);
				}
			}
		}
	}
	foreach ($model->getReferences() as $attribute) {
		$relation = $invoker->refer('attribute_relation', $attribute);
		if ('one-to-many' == $relation || 'one-to-one' == $relation) {
			$backreference = $invoker->refer('attribute_back_reference', $attribute);
			if (!$backreference) {
				$table = $invoker->refer('table_name', $model);
				$reftable = $invoker->refer('table_name', $attribute->getOwner());
				$column_name = sprintf('%s_id', $invoker->refer('table_name', $attribute->getOwner()));
				$result[] = sprintf('ALTER TABLE `%s` ADD CONSTRAINT `fk_%s` FOREIGN KEY (`%s`) REFERENCES `%s` (`id`)', $table, $column_name, $column_name, $reftable);
			}
		}
	}
	return $result;
});

$this->registerHelper('model_missing_references', function ($invoker, $model) 
{
	$result = array();
	foreach ($model->getReferences() as $attribute) {
		$relation = $invoker->refer('attribute_relation', $attribute);
		if ('one-to-many' == $relation || 'one-to-one' == $relation) {
			$backreference = $invoker->refer('attribute_back_reference', $attribute);
			if (!$backreference) {
				$result[] = $attribute;
			}
		}
	}
	return $result;
});

$this->registerHelper('many_many_tables', function ($invoker) 
{
	$result = array();
	$models = $invoker->getBuilder()->getModels();
	foreach ($models as $model) {
		foreach ($model->getAttributes() as $attribute) {
			if ($attribute->getIsCollection()) {
				if ($attribute->getType() == Codeforge\Attribute::TYPE_CUSTOM) {
					if ('many-to-many' == $invoker->refer('attribute_relation', $attribute)) {
						$link = $attribute->getHint('manymanylink');
						if ($link && preg_match('#([a-z0-9_]+)\s*[(]\s*([a-z0-9_]+)\s*,\s*([a-z0-9_]+)\s*[)]#i', $link, $matches)) {
							$table_name = $invoker->refer('table_name', $matches[1]);
							$fk1 = sprintf('%s_id', $matches[2]);
							$fk2 = sprintf('%s_id', $matches[3]);
							$amodel = $model->getName();
							$bmodel = $attribute->getCustomType();
						} else {
							$table_name = sprintf('%s_%s_%s', $invoker->refer('table_name', $model), $invoker->refer('table_name', $attribute->getCustomType()), $attribute->getName());
							$fk1 = sprintf('%s_id', $invoker->refer('table_name', $model));
							$fk2 = sprintf('%s_id', $invoker->refer('table_name', $attribute->getCustomType()));
							$amodel = $model->getName();
							$bmodel = $attribute->getCustomType();
						}
						$result[$table_name] = array($amodel, $bmodel, $table_name, $fk1, $fk2);
					}
				}
			}
		}
	}
	return array_values($result);
});

$this->registerHelper('escape_value', function($invoker, $val) 
{
	if (null !== $val) {
		if (is_bool($val)) {
			return $val ? '1' : '0';
		} elseif (is_numeric($val)) {
			return '"' . strval($val) . '"';
		} else {
			return '"' . addslashes($val) . '"';
		}
	}
	return 'NULL';
});

$this->registerHelper('optimal_option_len', function ($invoker, $options, $divisible=1) 
{
	$max = 0;
	foreach ($options as $option) {
		$max = max($max, strlen($option));
	}
	if ($divisible > 1 && $max % $divisible) {
		$max += $divisible - $max % $divisible;
	}
	return $max;
});

$this->registerHelper('table_name', function ($invoker, $model) 
{
	return strtolower(preg_replace('/([a-z])([A-Z])/', '\1_\2', is_string($model) ? $model : $model->getName()));
});
