<?php

$this->registerHelper('model_columns', function ($invoker, $model) 
{
	$result = array();
	$columns = array();
	$keys = array();
	foreach ($model->getAttributes() as $attribute) {
		if (!$attribute->getIsCollection()) {
			if ($attribute->getType() == Codeforge\Attribute::TYPE_CUSTOM) {
				if ('many-to-one' == $invoker->refer('attribute_relation', $attribute)) {
					$definition = 'INTEGER UNSIGNED';
					if ($attribute->getBoolHint('required')) {
						$definition .= ' NOT NULL';
					} else {
						$definition .= ' DEFAULT NULL';
					}
					$comment = sprintf('%s, many-to-one relation, foreign key refers to %s', $invoker->refer('attribute_label', $attribute), $attribute->getCustomType());
					$definition .= sprintf(' COMMENT %s', $invoker->referNamespace('::mysql', 'escape_value', $comment));
					$columns[] = sprintf('\'%s_id\' => \'%s\'', $attribute->getName(), $definition);
					$keys[] = sprintf('KEY `idx_%s_id` (`%s_id`)', $attribute->getName(), $attribute->getName());
				}
			} else {
				$definition = $invoker->referNamespace('::mysql', 'attribute_type', $attribute);
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
					$definition .= sprintf(' DEFAULT %s', $invoker->referNamespace('::mysql', 'escape_value', $default));
				}
				$comment = $invoker->refer('attribute_label', $attribute);
				$definition .= sprintf(' COMMENT %s', $invoker->referNamespace('::mysql', 'escape_value', $comment));
				$columns[] = sprintf('\'%s\' => \'%s\'', $attribute->getName(), $definition);
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
				$column_name = sprintf('%s_id', $invoker->referNamespace('::mysql', 'table_name', $attribute->getOwner()));
				$definition = 'INTEGER UNSIGNED';
				if ('one-to-one' == $relation) {
					$definition .= ' NOT NULL';
					$comment = sprintf('one-to-one relation, foreign key refers to %s', $attribute->getOwner()->getName());
				} else {
					$definition .= ' DEFAULT NULL';
					$comment = sprintf('many-to-one relation, foreign key refers to %s', $attribute->getOwner()->getName());
				}
				$definition .= sprintf(' COMMENT %s', $invoker->referNamespace('::mysql', 'escape_value', $comment));
				$columns[] = sprintf('\'%s\' => \'%s\'', $attribute->getName(), $definition);
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

$this->registerHelper('many_many_tables', function ($invoker) 
{
	return array(); //$invoker->referNamespace('::mysql', 'many_many_tables');
});

$this->registerHelper('table_name', function ($invoker, $model) 
{
	return $invoker->referNamespace('::mysql', 'table_name', $model);
});
