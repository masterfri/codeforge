<?php

class Formatter extends CFormatter
{
	public function formatDate($value)
	{
		if (empty($value)) {
			return null;
		}
		$timestamp = $this->normalizeDateValue($value);
		return $timestamp ? date($this->dateFormat, $timestamp) : null;
	}

	public function formatTime($value)
	{
		if (empty($value)) {
			return null;
		}
		$timestamp = $this->normalizeDateValue($value);
		return $timestamp ? date($this->timeFormat, $timestamp) : null;
	}

	public function formatDatetime($value)
	{
		if (empty($value)) {
			return null;
		}
		$timestamp = $this->normalizeDateValue($value);
		return $timestamp ? date($this->datetimeFormat, $timestamp) : null;
	}
	
	public function formatArray($value)
	{
		if (empty($value)) {
			return null;
		}
		return is_array($value) ? implode(', ', $value) : $value;
	}
	
	public function formatTextex($value)
	{
		$result = array();
		foreach (explode("\n", $value) as $line) {
			if ('' != trim($line)) {
				$result[] = preg_replace('#https?://[^\s]+#ie', 'CHtml::link(CHtml::encode("\0"),"\0")', $line);
			}
		}
		return implode('<br />', $result);
	}
}
