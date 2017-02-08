<?php

namespace App\Http\Requests;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Http\FormRequest;

class BaseFormRequest extends FormRequest
{
	public function authorize()
	{
		return true;
	}

	public function sanitizers()
	{
		return [];
	}
	
	public function sanitize()
	{
		$data = $this->all();
		$sanitizers = $this->prepareSanitizers();
		return $this->applySanitizers($data, $sanitizers);
	}
	
	protected function applySanitizers(&$data, &$sanitizers)
	{
		$result = [];
		foreach ($data as $key => $value) {
			if (isset($sanitizers[$key])) {
				$result[$key] = $this->sanitizeValue($value, $sanitizers[$key]);
			} elseif (isset($sanitizers['*'])) {
				$result[$key] = $this->sanitizeValue($value, $sanitizers['*']);
			} else {
				$result[$key] = $value;
			}
		}
		return $result;
	}
	
	protected function sanitizeValue(&$data, &$sanitizers)
	{
		if ($sanitizers instanceof Collection) {
			$result = $data;
			foreach ($sanitizers as $sanitizer) {
				if (!method_exists($this, $sanitizer[0])) {
					throw new \Exception('Undefined method ' . get_class($this) . '::' . $sanitizer[0]);
				}
				$params = $sanitizer[1];
				array_unshift($params, $result);
				$result = call_user_func_array([$this, $sanitizer[0]], $params);
			}
			return $result;
		} elseif (is_array($data)) {
			return $this->applySanitizers($data, $sanitizers);
		} else {
			return $data;
		}
	}
	
	protected function prepareSanitizers()
	{
		$prepared = [];
		foreach ($this->sanitizers() as $attribute => $sanitizer) {
			if (is_array($sanitizer)) {
				Arr::set($prepared, $attribute, $this->parseSanitizerArray($sanitizer));
			} else {
				Arr::set($prepared, $attribute, $this->parseSanitizerString($sanitizer));
			}
		}
		return $prepared;
	}
	
	protected function parseSanitizerArray($array)
	{
		return collect($array)->map(function($item) {
			return [
				'sanitize' . Str::studly($item[0]),
				array_slice($item, 1),
			];
		});
	}
	
	protected function parseSanitizerString($string)
	{
		return collect(explode('|', $string))->map(function($item) {
			$nameParams = explode(':', $item);
			return [
				'sanitize' . Str::studly($nameParams[0]),
				isset($nameParams[1]) ? explode(',', $nameParams[1]) : [],
			];
		});
	}
	
	protected function sanitizeNullable($value)
	{
		return $value === '' ? null : $value;
	}
	
	protected function sanitizeInteger($value)
	{
		return $value === null ? null : ($value === '' ? 0 : intval($value));
	}
	
	protected function sanitizeFloat($value)
	{
		return $value === null ? null : ($value === '' ? 0 : floatval($value));
	}
	
	protected function sanitizeBoolean($value)
	{
		return $value === null ? null : ($value ? 1 : 0);
	}
	
	protected function sanitizeTrim($value)
	{
		return $value === null ? null : trim($value);
	}
}
