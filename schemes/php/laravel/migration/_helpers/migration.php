<?php

$this->registerHelper('migration_prefix', function ($invoker, $model) 
{
	return date('Y_m_d_His', $model->getHint('timeCreated', time()));
});

$this->registerHelper('migration_name', function ($invoker, $table) 
{
	return sprintf('create_%s_table', $table);
});

$this->registerHelper('migration_class', function ($invoker, $table) 
{
	return sprintf('Create%sTable', implode('', array_map('ucfirst', explode('_', $table))));
});

$this->registerHelper('optimal_option_len', function ($invoker, $options, $divisible=1) 
{
	$max = $divisible;
	foreach ($options as $option) {
		$max = max($max, strlen($option));
	}
	if ($divisible > 1 && $max % $divisible) {
		$max += $divisible - $max % $divisible;
	}
	return $max;
});

$this->registerHelper('link_tables', function ($invoker) 
{
	$result = array();
	$models = $invoker->getBuilder()->getModels();
	foreach ($models as $model) {
		foreach ($model->getAttributes() as $attribute) {
			if ($attribute->getIsCollection()) {
				if ($attribute->getType() == Codeforge\Attribute::TYPE_CUSTOM) {
					if ('belongs-to-many' == $invoker->refer('attribute_relation', $attribute)) {
						$amodel = $model;
						$bmodel = $invoker->getBuilder()->getModel($attribute->getCustomType());
						if ($bmodel !== null) {
							$table_name = $invoker->refer('pivot_table', $amodel, $bmodel, $attribute);
							$fk1 = $invoker->refer('foreign_key', $amodel);
							$fk2 = $invoker->refer('foreign_key', $bmodel);
							if (strcmp($fk1, $fk2) < 0) {
								$result[$table_name] = array($amodel, $bmodel, $table_name, $fk1, $fk2);
							} else {
								$result[$table_name] = array($bmodel, $amodel, $table_name, $fk2, $fk1);
							}
						}
					}
				}
			}
		}
	}
	return array_values($result);
});

$this->registerHelper('column_definitions', function ($invoker, $attribute) 
{
	$definitions = array();
	if (!$attribute->getIsCollection()) {
		$attribute_id = $invoker->refer('attribute_id', $attribute);
		if ($attribute->getType() == Codeforge\Attribute::TYPE_CUSTOM) {
			if ('belongs-to-one' == $invoker->refer('attribute_relation', $attribute)) {
				$definition = sprintf('$table->integer(\'%s\')->unsigned()', $attribute_id);
				if(!$attribute->getBoolHint('required')) {
					$definition .= '->nullable()';
				}
				$definitions[] = $definition;
				$definitions[] = sprintf('$table->index(\'%s\', \'idx_%s\')', $attribute_id, $attribute_id);
			}
		} else {
			if ($attribute->getType() == Codeforge\Attribute::TYPE_INT) {
				$definition = sprintf('$table->integer(\'%s\')', $attribute_id);
				if ($attribute->getIsUnsigned()) {
					$definition .= '->unsigned()';
				}
				if(!$attribute->getBoolHint('required')) {
					$definition .= '->nullable()';
				}
				$definitions[] = $definition;
			} elseif ($attribute->getType() == Codeforge\Attribute::TYPE_DECIMAL) {
				$definition = sprintf('$table->decimal(\'%s\'', $attribute_id);
				if (is_array($attribute->getSize())) {
					$definition .= ', ' . implode(', ', $attribute->getSize());
				}
				$definition .= ')';
				if ($attribute->getIsUnsigned()) {
					$definition .= '->unsigned()';
				}
				if(!$attribute->getBoolHint('required')) {
					$definition .= '->nullable()';
				}
				$definitions[] = $definition;
			} elseif ($attribute->getType() == Codeforge\Attribute::TYPE_CHAR) {
				$definition = sprintf('$table->string(\'%s\'', $attribute_id);
				if ($attribute->getSize()) {
					$definition .= ', ' . $attribute->getSize();
				}
				$definition .= ')';
				if(!$attribute->getBoolHint('required')) {
					$definition .= '->nullable()';
				}
				$definitions[] = $definition;
			} elseif ($attribute->getType() == Codeforge\Attribute::TYPE_TEXT) {
				$definition = sprintf('$table->text(\'%s\')', $attribute_id);
				if(!$attribute->getBoolHint('required')) {
					$definition .= '->nullable()';
				}
				$definitions[] = $definition;
			} elseif ($attribute->getType() == Codeforge\Attribute::TYPE_BOOL) {
				$definition = sprintf('$table->tinyInteger(\'%s\')->unsigned()', $attribute_id);
				if(!$attribute->getBoolHint('required')) {
					$definition .= '->nullable()';
				}
				$definitions[] = $definition;
			} elseif ($attribute->getType() == Codeforge\Attribute::TYPE_INTOPTION) {
				$definition = sprintf('$table->integer(\'%s\')', $attribute_id);
				if(!$attribute->getBoolHint('required')) {
					$definition .= '->nullable()';
				}
				$definitions[] = $definition;
			} elseif ($attribute->getType() == Codeforge\Attribute::TYPE_STROPTION) {
				$definition = sprintf('$table->string(\'%s\', %d)', $attribute_id, $invoker->refer('optimal_option_len', $attribute->getOptions(), 10));
				if(!$attribute->getBoolHint('required')) {
					$definition .= '->nullable()';
				}
				$definitions[] = $definition;
			} else {
				return array();
			}
			if ($attribute->getBoolHint('searchable') || $attribute->getBoolHint('index')) {
				$definitions[] = sprintf('$table->index(\'%s\', \'idx_%s\')', $attribute_id, $attribute_id);
			}
		}
	}
	return $definitions;
});