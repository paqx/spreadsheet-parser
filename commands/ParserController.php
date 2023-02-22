<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as SpreadsheetException;

class ParserController extends Controller
{
	/**
	 * Type of the spreadsheet to be parsed: Xlsx, Ods, etc.
	 */
	public $docType;
	
	/**
	 * Name of the active sheet
	 */
	public $sheetName;
	
	/**
	 * Range of cells to be parsed, e.g. A3:N3, where A3 is the upper left 
	 * corner and N3 is the bottom right corner of the area
	 */
	public $range;
	
	/**
	 * This is a so-called control column. It is used to:
	 * 1. Determine if the current row defines a new category
	 * 2. Check if the current row already exists in the database
	 */
	public $ctrlColumn;
	
	/**
	 * The option is used to ignore certain rows. A list of comma separated 
	 * pairs of values should be passed, e.g. "A:,A:Total" means that if a 
	 * cell in the A column is either empty or equals to "Total", the row 
	 * must be skipped
	 */
	public $ignore;
	
	/**
	 * The option can be used to dry-run the parser
	 */
	public $mode;
	
	/**
	 * The option provides additional mapping of the spreadsheet columns to 
	 * the database table, e.g. A:product says that the A column of the 
	 * spreadsheet should be mapped to the product columns of the database 
	 * table
	 */
	public $mapping;
	
	/**
	 * The name of the model that should be used to store data in the database
	 */
	public $model;

	public function options($actionID) 
	{
		switch ($actionID) {
			case 'run':
				return [
					'docType',
					'sheetName',
					'range',
					'ctrlColumn',
					'ignore',
					'mode',
					'mapping',
					'model'
				];

			default:
				return [
					
				];
		}
	}
	
    public function actionRun(string $fileName)
    {
		try {
			$reader = IOFactory::createReader($this->docType);
			$reader->setReadDataOnly(true);
			
			$this->stdout("Loading file ".$fileName.PHP_EOL);
			$document = $reader->load($fileName);
			
			$this->stdout("Switching to sheet ".$this->sheetName.PHP_EOL);
			$document->setActiveSheetIndexByName($this->sheetName);
			
			$this->stdout("Fetching selected range ".$this->range.PHP_EOL);
			$rows = $document->getActiveSheet()
					->rangeToArray(
						$this->range,
						null,
						true,
						false,
						true
					);
		} catch (SpreadsheetException $ex) {
			$this->stdout($ex->getMessage().PHP_EOL);
			
			return ExitCode::UNSPECIFIED_ERROR;
		}

		$first = true;
		$headers = [];
		$category = '';
		$categories = [];
		$ignoreRules = [];
		$mappingRules = [];
		$index = 0;
		$rowsCreated = 0;
		$rowsUpdated = 0;
		$dryrun = ($this->mode == 'dryrun') ? true : false;
		
		if (isset($this->ignore)) {
			$ignoreArr = explode(',', $this->ignore);
			
			foreach ($ignoreArr as $ignoreItem) {
				$ignoreRule = explode(':', $ignoreItem);
				array_push($ignoreRules, $ignoreRule);
			}
		}
		
		if (isset($this->mapping)) {
			$mappingArr = explode(',', $this->mapping);
			
			foreach ($mappingArr as $mappingItem) {
				$mappingRule = explode(':', $mappingItem);
				array_push($mappingRules, $mappingRule);
			}
		}
		
		foreach ($rows as $row) {
			if ($first) {
				$headers = $row;
				$first = false;
				
				foreach ($headers as $key => $value) {
					$headers[$key] = strtolower(str_replace(' ', '_', $value));
				}
				
				foreach ($mappingRules as $mappingRule) {
					$mappingKey = $mappingRule[0];
					$mappingValue = $mappingRule[1];

					if (array_key_exists($mappingKey, $headers)) {
						$headers[$mappingKey] = $mappingValue;
					}
				}

				continue;
			}
			
			if ($this->isIgnored($row, $ignoreRules)) {
				$this->stdout("Ignoring row with index ".$index." [".$row[array_key_first($row)]."]".PHP_EOL);
				
				continue;
			}
			
			if ($this->isCategory($row, $this->ctrlColumn)) {
				$category = $row[$this->ctrlColumn];
				array_push($categories, $category);
				
				continue;
			}

			$modelName = "\\app\\models\\".$this->model;
			$model = $modelName::findOne([
				$headers[$this->ctrlColumn] => $row[$this->ctrlColumn],
				'category' => $category
			]);
			
			if (empty($model)) {
				$new = true;
				$model = new $modelName;
			} else {
				$new = false;
			}
			
			foreach ($row as $key => $value) {
				if ($model->hasAttribute($headers[$key])) {
					$model->{$headers[$key]} = $value;
					$model->category = $category;
				}
			}
			
			if ($new) {
				$dryrun ?: $model->save();
				$rowsCreated++;
			} else {
				$dryrun ?: $model->update() ;
				$rowsUpdated++;
			}

			$index++;
		}
		
		$this->stdout(PHP_EOL);
		$this->stdout("Found the following categories:".PHP_EOL);
		
		foreach ($categories as $key => $value) {
			$this->stdout(($key + 1).". ".$value.PHP_EOL);
		}
		
		$this->stdout(PHP_EOL);
		$this->stdout($rowsCreated." new rows ".($dryrun ? "need to be" : "have been")." created".PHP_EOL);
		$this->stdout($rowsUpdated." existing rows ".($dryrun ? "need to be" : "have been")." updated".PHP_EOL);

        return ExitCode::OK;
    }
	
	private function isIgnored(array $row, array $rules): bool
	{
		$ignore = false;

		foreach ($rules as $rule) {
			$ruleKey = $rule[0];
			$ruleValue = $rule[1];
			
			if (array_key_exists($ruleKey, $row)) {
				if ($ruleValue == '') {
					$ruleValue = null;
				}
				
				if ($row[$ruleKey] == $ruleValue) {
					$ignore = true;
				}
			}
		}
		
		return $ignore;
	}

	private function isCategory(array $row, string $column): bool
	{
		$category = true;
		
		foreach ($row as $key => $value) {
			if ($key != $column && !is_null($value)) {
				$category = false;
			}
		}
		
		return $category;
	}
}
